<?php

namespace App\Services;

use App\Models\Address;

class AddressServices
{
    /**
     * store
     *
     * @param  mixed  $input
     * @return void
     */
    public function store($input)
    {
        return Address::updateOrCreate(
            [
                'id' => $input['id'] ?? null,   // condition
            ],
            [
                'facility' => $input['facility'] ?? '',
                'address_1' => $input['address'] ?? '',
                'city' => $input['city'] ?? '',
                'state' => $input['state'] ?? '',
                'phone' => $input['phone'] ?? '',
                'zip' => $input['pin_code'] ?? '',
                'email' => $input['email'] ?? '',
                'comment' => $input['comment'] ?? '',
                'ordering_id' => $input['ordering_id'] ?? '',
                'electronic_order' => $input['electronic_order'] ?? '',
            ]
        );
    }
}
