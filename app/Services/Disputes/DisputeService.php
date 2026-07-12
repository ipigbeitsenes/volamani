<?php

namespace App\Services\Disputes;

use App\Actions\Disputes\AddDisputeMessageAction;
use App\Actions\Disputes\EscalateDisputeAction;
use App\Actions\Disputes\OpenDisputeAction;
use App\Actions\Disputes\ResolveDisputeAction;
use App\Enums\DisputeResolution;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Escrow;
use App\Models\User;
use App\Repositories\Disputes\DisputeRepository;
use Illuminate\Http\UploadedFile;

class DisputeService
{
    public function __construct(
        private OpenDisputeAction $openAction,
        private AddDisputeMessageAction $messageAction,
        private ResolveDisputeAction $resolveAction,
        private EscalateDisputeAction $escalateAction,
        private DisputeRepository $repo,
    ) {}

    public function open(Escrow $escrow, User $raisedBy, array $data, ?UploadedFile $attachment = null): Dispute
    {
        return $this->openAction->execute(
            $escrow,
            $raisedBy,
            $data['reason'],
            $data['description'],
            $attachment
        );
    }

    public function addMessage(Dispute $dispute, User $sender, string $message, ?UploadedFile $attachment = null, bool $isStaff = false): DisputeMessage
    {
        return $this->messageAction->execute($dispute, $sender, $message, $attachment, $isStaff);
    }

    public function resolve(Dispute $dispute, User $admin, DisputeResolution $resolution, ?int $vendorShareKobo = null, ?string $note = null): Dispute
    {
        return $this->resolveAction->execute($dispute, $admin, $resolution, $vendorShareKobo, $note);
    }

    public function escalate(Dispute $dispute, User $admin, ?string $note = null): Dispute
    {
        return $this->escalateAction->execute($dispute, $admin, $note);
    }

    // ─── Query passthroughs ─────────────────────────────────────────────────────

    public function forUser(User $user, int $perPage = 15)
    {
        return $this->repo->forUser($user, $perPage);
    }

    public function allForAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->allForAdmin($perPage, $filters);
    }

    public function openCount(): int
    {
        return $this->repo->openCount();
    }
}
