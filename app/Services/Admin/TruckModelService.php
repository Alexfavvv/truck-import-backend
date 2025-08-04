<?php

namespace App\Services\Admin;

use App\Models\TruckModel;
use Illuminate\Support\Facades\DB;

class TruckModelService
{
    public static function store(array $data): TruckModel
    {
        try {
            DB::beginTransaction();
            $truckModel = TruckModel::create($data['truckModel']);
            $truckModel->metatags()->create($data['meta']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            abort(500, $e->getMessage());
        }

        return $truckModel;
    }

    public static function update(array $data, TruckModel $truckModel): TruckModel
    {
        try {
            DB::beginTransaction();
            $truckModel->update($data['truckModel']);
            $truckModel->metatags()->update($data['meta']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            abort(500, $e->getMessage());
        }

        return $truckModel;
    }
}
