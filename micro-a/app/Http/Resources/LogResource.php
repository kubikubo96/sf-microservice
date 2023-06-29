<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'log_time' => $this->log_time,
            'payload' => $this->payload,
            'ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'source' => $this->source,
            'object_type' => $this->object_type,
            'object_action' => $this->object_action,
            'extra' => LogExtraResource::collection($this->log_extras),
            'service' => $this->service,
        ];
    }
}
