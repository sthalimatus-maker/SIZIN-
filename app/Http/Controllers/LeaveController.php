<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveStatusHistory;
use App\Models\NotificationLog;
use App\Services\LeaveReportPdf;
use App\Services\WhatsAppCloudApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LeaveController extends Controller
{
    public function studentDashboard(Request $request)
    {
        $requests = LeaveRequest::where('student_id', $request->user()->id)->latest()->limit(5)->get();
        $counts = LeaveRequest::where('student_id', $request->user()->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('student.dashboard', compact('requests', 'counts'));
    }

    public function indexStudent(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:semua,menunggu,disetujui,ditolak,keluar,selesai'],
        ]);
        $query = LeaveRequest::where('student_id', $request->user()->id);

        if (($filters['status'] ?? 'semua') !== 'semua') $query->where('status', $filters['status']);

        $requests = $query->latest()->paginate(12)->withQueryString();
        return view('student.requests.index', compact('requests', 'filters'));
    }

    public function create() { return view('student.requests.form', ['leaveRequest' => null]); }

    public function store(Request $request)
    {
        $data = $this->validatedRequest($request);
        $data['student_id'] = $request->user()->id;
        if ($request->hasFile('evidence')) $data['evidence_path'] = $request->file('evidence')->store('leave-evidence', 'local');
        unset($data['evidence']);

        DB::transaction(function () use ($data, $request, &$leaveRequest) {
            $leaveRequest = LeaveRequest::create($data);
            $this->history($leaveRequest, $request->user()->id, 'menunggu', 'Pengajuan dibuat siswa.');
        });

        return redirect()->route('student.requests.index')->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function showStudent(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->student_id === $request->user()->id, 403);
        $leaveRequest->load(['histories.actor', 'reviewer']);
        return view('student.requests.show', compact('leaveRequest'));
    }

    public function evidence(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($request->user()->isTeacher() || $leaveRequest->student_id === $request->user()->id, 403);
        abort_unless($leaveRequest->evidence_path, 404);

        $disk = collect(['local', 'public'])->first(fn ($disk) => Storage::disk($disk)->exists($leaveRequest->evidence_path));
        abort_unless($disk, 404, 'Berkas lampiran tidak ditemukan.');

        return response()->file(Storage::disk($disk)->path($leaveRequest->evidence_path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function edit(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->student_id === $request->user()->id && $leaveRequest->status === 'menunggu', 403);
        return view('student.requests.form', compact('leaveRequest'));
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->student_id === $request->user()->id && $leaveRequest->status === 'menunggu', 403);
        $data = $this->validatedRequest($request);
        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('leave-evidence', 'local');
            if ($leaveRequest->evidence_path) {
                Storage::disk('local')->delete($leaveRequest->evidence_path);
                Storage::disk('public')->delete($leaveRequest->evidence_path);
            }
        }
        unset($data['evidence']);
        $leaveRequest->update($data);
        $this->history($leaveRequest, $request->user()->id, 'menunggu', 'Pengajuan diperbarui siswa.');
        return redirect()->route('student.requests.index')->with('success', 'Pengajuan berhasil diperbarui.');
    }

    public function destroy(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->student_id === $request->user()->id && $leaveRequest->status === 'menunggu', 403);
        if ($leaveRequest->evidence_path) {
            Storage::disk('local')->delete($leaveRequest->evidence_path);
            Storage::disk('public')->delete($leaveRequest->evidence_path);
        }
        $leaveRequest->delete();
        return redirect()->route('student.requests.index')->with('success', 'Pengajuan berhasil dihapus.');
    }

    public function teacherDashboard()
    {
        $counts = [
            'menunggu' => LeaveRequest::where('status', 'menunggu')->count(),
            'disetujui' => LeaveRequest::where('status', 'disetujui')->whereDate('updated_at', today())->count(),
            'keluar' => LeaveRequest::whereNotNull('actual_exit_at')->whereNull('actual_return_at')->count(),
            'selesai' => LeaveRequest::whereNotNull('actual_return_at')->whereDate('actual_return_at', today())->count(),
        ];
        $requests = LeaveRequest::with('student')->latest()->limit(8)->get();
        return view('teacher.dashboard', compact('counts', 'requests'));
    }

    public function verification(Request $request)
    {
        $requests = LeaveRequest::with('student')->where('status', 'menunggu')->latest()->paginate(15)->withQueryString();
        $selected = $request->integer('selected') ? LeaveRequest::with(['student', 'histories.actor'])->findOrFail($request->integer('selected')) : $requests->first();
        $selected?->loadMissing(['student', 'histories.actor']);
        return view('teacher.verification', compact('requests', 'selected'));
    }

    public function statusHistory(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['student', 'histories.actor']);

        return view('teacher.history', compact('leaveRequest'));
    }

    public function decide(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->status === 'menunggu', 422, 'Pengajuan ini sudah diverifikasi.');
        $data = $request->validate([
            'decision' => ['required', 'in:disetujui,ditolak'],
            'rejection_reason' => ['nullable', 'required_if:decision,ditolak', 'string', 'max:1000'],
        ]);
        $notificationLogs = DB::transaction(function () use ($leaveRequest, $request, $data) {
            $leaveRequest->update(['status' => $data['decision'], 'reviewed_by' => $request->user()->id, 'rejection_reason' => $data['rejection_reason'] ?? null]);
            $this->history($leaveRequest, $request->user()->id, $data['decision'], $data['rejection_reason'] ?? 'Pengajuan diverifikasi guru.');
            $student = $leaveRequest->student;
            $message = 'Izin ' . $student->name . ' (' . $leaveRequest->type . ') berstatus ' . $data['decision'] . '. Tanggal keluar: ' . $leaveRequest->leave_date->format('d-m-Y') . ', tujuan: ' . $leaveRequest->destination . '.';

            return collect([
                NotificationLog::create([
                    'leave_request_id' => $leaveRequest->id,
                    'channel' => 'whatsapp',
                    'recipient' => $student->phone ?: 'Nomor wali belum diisi',
                    'message' => $message,
                    'status' => $student->phone ? 'menunggu' : 'gagal',
                    'response' => $student->phone ? 'Menunggu pengiriman.' : 'Nomor WhatsApp wali belum tersedia.',
                ]),
            ]);
        });

        foreach ($notificationLogs as $notificationLog) {
            $this->deliverNotification($notificationLog->load('request.student'));
        }

        return back()->with('success', 'Status pengajuan diperbarui menjadi ' . $data['decision'] . '.');
    }

    public function movements()
    {
        $approved = LeaveRequest::with('student')->where('status', 'disetujui')->whereNull('actual_exit_at')->orderBy('leave_date')->get();
        $transactions = LeaveRequest::with('student')->whereNotNull('actual_exit_at')
            ->where(function ($query) {
                $query->whereNull('actual_return_at')
                    ->orWhereDate('actual_exit_at', today())
                    ->orWhereDate('actual_return_at', today());
            })
            ->latest('actual_exit_at')->get();

        return view('teacher.movements', compact('approved', 'transactions'));
    }

    public function recordExit(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->status === 'disetujui' && ! $leaveRequest->actual_exit_at, 422);
        $data = $request->validate(['actual_exit_at' => ['required', 'date'], 'exit_note' => ['nullable', 'string', 'max:1000']]);
        $leaveRequest->update($data + ['status' => 'keluar']);
        $this->history($leaveRequest, $request->user()->id, 'keluar', 'Waktu keluar dicatat.');
        return redirect()->route('teacher.movements')->with('success', 'Waktu keluar tersimpan.');
    }

    public function recordReturn(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->actual_exit_at && ! $leaveRequest->actual_return_at, 422);
        $data = $request->validate(['actual_return_at' => ['required', 'date'], 'return_note' => ['nullable', 'string', 'max:1000']]);
        abort_unless(strtotime($data['actual_return_at']) >= $leaveRequest->actual_exit_at->timestamp, 422, 'Waktu kembali harus setelah waktu keluar.');
        $leaveRequest->update($data + ['status' => 'selesai']);
        $this->history($leaveRequest, $request->user()->id, 'selesai', 'Waktu kembali dicatat.');
        return redirect()->route('teacher.movements')->with('success', 'Waktu kembali tersimpan.');
    }

    public function notifications()
    {
        $notifications = NotificationLog::with('request.student')->latest()->paginate(20);
        return view('teacher.notifications', compact('notifications'));
    }

    public function retryNotification(NotificationLog $notificationLog)
    {
        abort_if($notificationLog->status === 'terkirim', 422, 'Notifikasi ini sudah terkirim.');
        $notificationLog->load('request.student');
        $this->deliverNotification($notificationLog);

        return back()->with(
            $notificationLog->fresh()->status === 'terkirim' ? 'success' : 'error',
            $notificationLog->fresh()->status === 'terkirim' ? 'Notifikasi berhasil dikirim.' : 'Pengiriman gagal: ' . $notificationLog->fresh()->response,
        );
    }

    public function report(Request $request)
    {
        $filters = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'status' => ['nullable', 'in:semua,menunggu,disetujui,ditolak,keluar,selesai']]);
        $query = LeaveRequest::with('student')->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('leave_date', '>=', $date))->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('leave_date', '<=', $date));
        if (($filters['status'] ?? 'semua') !== 'semua') $query->where('status', $filters['status']);
        $requests = $query->orderByDesc('leave_date')->paginate(30)->withQueryString();
        return view('teacher.report', compact('requests', 'filters'));
    }

    public function reportPdf(Request $request, LeaveReportPdf $pdf)
    {
        $filters = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from'], 'status' => ['nullable', 'in:semua,menunggu,disetujui,ditolak,keluar,selesai']]);
        $query = LeaveRequest::with('student')->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('leave_date', '>=', $date))->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('leave_date', '<=', $date));
        if (($filters['status'] ?? 'semua') !== 'semua') $query->where('status', $filters['status']);

        $requests = $query->orderByDesc('leave_date')->get();
        $filename = 'laporan-izin-'.now()->format('Ymd-His').'.pdf';

        return response()->streamDownload(
            fn () => print($pdf->generate($requests, $filters)),
            $filename,
            ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store'],
        );
    }

    private function validatedRequest(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:Sakit,Keperluan keluarga,Keperluan lain'],
            'destination' => ['required', 'string', 'max:180'], 'reason' => ['required', 'string', 'max:2000'],
            'leave_date' => ['required', 'date'], 'leave_time' => ['required', 'date_format:H:i'],
            'return_date' => ['required', 'date', 'after_or_equal:leave_date'], 'return_time' => ['required', 'date_format:H:i'],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
    }

    private function history(LeaveRequest $leaveRequest, ?int $actor, string $status, ?string $note): void
    {
        LeaveStatusHistory::create(['leave_request_id' => $leaveRequest->id, 'changed_by' => $actor, 'status' => $status, 'note' => $note]);
    }

    private function deliverNotification(NotificationLog $notificationLog): void
    {
        if (! $notificationLog->request || ! $notificationLog->request->student) {
            $notificationLog->update(['status' => 'gagal', 'response' => 'Data pengajuan atau akun siswa tidak ditemukan.']);
            return;
        }

        try {
            if ($notificationLog->channel === 'email') {
                $result = ['sent' => false, 'detail' => 'Email hanya digunakan untuk reset password. Notifikasi status dikirim melalui WhatsApp.'];
            } else {
                $result = app(WhatsAppCloudApi::class)->sendTemplate($notificationLog->recipient, $notificationLog->message);
            }
        } catch (Throwable $exception) {
            report($exception);
            $result = ['sent' => false, 'detail' => 'Pengiriman gagal: ' . $exception->getMessage()];
        }

        $notificationLog->update([
            'status' => $result['sent'] ? 'terkirim' : 'gagal',
            'response' => $result['detail'],
            'sent_at' => $result['sent'] ? now() : null,
        ]);
    }
}
