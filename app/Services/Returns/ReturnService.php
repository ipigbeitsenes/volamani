<?php

namespace App\Services\Returns;

use App\Actions\Returns\ApproveReturnAction;
use App\Actions\Returns\CancelReturnAction;
use App\Actions\Returns\ConfirmReturnAction;
use App\Actions\Returns\MarkReturnShippedAction;
use App\Actions\Returns\RejectReturnAction;
use App\Actions\Returns\RequestReturnAction;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Returns\ReturnRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReturnService extends BaseService
{
    public function __construct(
        private ReturnRepository        $repo,
        private RequestReturnAction     $requestAction,
        private ApproveReturnAction     $approveAction,
        private RejectReturnAction      $rejectAction,
        private MarkReturnShippedAction $shippedAction,
        private ConfirmReturnAction     $confirmAction,
        private CancelReturnAction      $cancelAction,
    ) {}

    public function request(Order $order, User $buyer, array $data): ReturnRequest
    {
        return $this->requestAction->execute($order, $buyer, $data);
    }

    public function approve(ReturnRequest $return, User $actor, ?string $note = null): ReturnRequest
    {
        return $this->approveAction->execute($return, $actor, $note);
    }

    public function reject(ReturnRequest $return, User $actor, string $note): ReturnRequest
    {
        return $this->rejectAction->execute($return, $actor, $note);
    }

    public function markShipped(ReturnRequest $return, ?string $tracking): ReturnRequest
    {
        return $this->shippedAction->execute($return, $tracking);
    }

    public function confirm(ReturnRequest $return, User $actor): ReturnRequest
    {
        return $this->confirmAction->execute($return, $actor);
    }

    public function cancel(ReturnRequest $return): ReturnRequest
    {
        return $this->cancelAction->execute($return);
    }

    // ─── Queries ────────────────────────────────────────────────────────────────

    public function forBuyer(User $user): LengthAwarePaginator
    {
        return $this->repo->forBuyer($user);
    }

    public function forVendor(Vendor $vendor, array $filters = []): LengthAwarePaginator
    {
        return $this->repo->forVendor($vendor, $filters);
    }

    public function allForAdmin(array $filters = []): LengthAwarePaginator
    {
        return $this->repo->allForAdmin($filters);
    }

    public function pendingCountForVendor(Vendor $vendor): int
    {
        return $this->repo->pendingCountForVendor($vendor);
    }
}
