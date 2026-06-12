<?php

namespace App\Services\Services;

use App\Actions\Services\AcceptDeliveryAction;
use App\Actions\Services\CreateServiceAction;
use App\Actions\Services\DeliverServiceOrderAction;
use App\Actions\Services\PlaceServiceOrderAction;
use App\Actions\Services\RequestRevisionAction;
use App\Actions\Services\SubmitRequirementsAction;
use App\Actions\Services\UpdateServiceAction;
use App\Enums\ProductStatus;
use App\Models\FreelanceService;
use App\Models\ServiceOrder;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Vendor;
use App\Services\BaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ServiceListingService extends BaseService
{
    public function __construct(
        private CreateServiceAction     $createAction,
        private UpdateServiceAction     $updateAction,
        private PlaceServiceOrderAction $placeOrderAction,
        private SubmitRequirementsAction $requirementsAction,
        private DeliverServiceOrderAction $deliverAction,
        private RequestRevisionAction   $revisionAction,
        private AcceptDeliveryAction    $acceptAction,
    ) {}

    public function createService(Vendor $vendor, array $data): FreelanceService
    {
        return $this->createAction->execute($vendor, $data);
    }

    public function updateService(FreelanceService $service, array $data): FreelanceService
    {
        return $this->updateAction->execute($service, $data);
    }

    public function archiveService(FreelanceService $service): void
    {
        $service->update(['status' => ProductStatus::Archived]);
    }

    public function approveService(FreelanceService $service, User $admin): FreelanceService
    {
        $service->update([
            'status'           => ProductStatus::Active,
            'approved_at'      => now(),
            'approved_by'      => $admin->id,
            'rejection_reason' => null,
        ]);

        return $service;
    }

    public function rejectService(FreelanceService $service, User $admin, string $reason): FreelanceService
    {
        $service->update([
            'status'           => ProductStatus::Rejected,
            'rejection_reason' => $reason,
            'approved_by'      => $admin->id,
        ]);

        return $service;
    }

    public function placeOrder(FreelanceService $service, ServicePackage $package, User $buyer): ServiceOrder
    {
        return $this->placeOrderAction->execute($service, $package, $buyer);
    }

    public function submitRequirements(ServiceOrder $order, string $requirements): ServiceOrder
    {
        return $this->requirementsAction->execute($order, $requirements);
    }

    public function deliver(ServiceOrder $order, string $message, ?UploadedFile $attachment = null): void
    {
        $this->deliverAction->execute($order, $message, $attachment);
    }

    public function requestRevision(ServiceOrder $order, string $feedback): ServiceOrder
    {
        return $this->revisionAction->execute($order, $feedback);
    }

    public function acceptDelivery(ServiceOrder $order): ServiceOrder
    {
        return $this->acceptAction->execute($order);
    }

    public function sendMessage(ServiceOrder $order, User $sender, string $message, ?UploadedFile $attachment = null): void
    {
        $attachmentPath = null;
        $attachmentName = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('service-orders/' . $order->id, 'public');
            $attachmentName = $attachment->getClientOriginalName();
        }

        $order->messages()->create([
            'sender_id'       => $sender->id,
            'message'         => $message,
            'attachment'      => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);
    }
}
