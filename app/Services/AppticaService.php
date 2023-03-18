<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class AppticaService
{
    /**
     * @throws Exception
     */
    public function getTopHistory(string $applicationId, int $countryId, string $dateFrom, string $dateTo)
    {
        $response = Http::get("https://api.apptica.com/package/top_history/{$applicationId}/{$countryId}", [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'B4NKGg' => 'fVN5Q9KVOlOHDx9mOsKPAQsFBlEhBOwguLkNEDTZvKzJzT3l', // todo - Это нужно вынести в конструктор
        ])->json();

        if (402 == $response['status_code']) {
            throw new Exception(
                'The selected date is unavailable. The data is only available for the last 30 days.'
            );
        }

        return $response;
    }

    /**
     * /**
     * Change response format to - "date.categoryId.position"
     *
     * @param array $appticaResponse - Response from Apptica
     *
     * @throws Exception
     */
    public function formatResponse(array $appticaResponse): array
    {
        if (!array_key_exists('data', $appticaResponse)) {
            throw new Exception('Unable to get data from apptica response');
        }

        $formattedResponse = [];

        foreach ($appticaResponse['data'] as $categoryId => $category) {
            foreach ($category as $subCategory) {
                foreach ($subCategory as $positionDate => $position) {
                    if (!array_key_exists($positionDate, $formattedResponse)) {
                        $formattedResponse[$positionDate] = [];
                    }

                    if (!is_null($position)) {
                        $formattedResponse[$positionDate][$categoryId] =
                            array_key_exists($categoryId, $formattedResponse[$positionDate]) ?
                                min($formattedResponse[$positionDate][$categoryId], $position) : $position;
                    }
                }
            }
        }

        return $formattedResponse;
    }
}
