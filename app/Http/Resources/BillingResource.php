<?php

namespace App\Http\Resources;

use App\Helpers\DateTimeHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['amount'] = number_format($this->amount, 2);
        $data['updated_at'] = DateTimeHelper::formatDate($this->updated_at);
        $data['created_at'] = DateTimeHelper::formatDate($this->created_at);

        return $data;
    }
}
