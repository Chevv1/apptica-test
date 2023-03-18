<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAppTopPositionsRequest;
use App\Models\App;
use App\Services\AppService;
use App\Services\AppticaService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AppController extends Controller
{
    public function __construct(
        private readonly AppticaService $appticaService,
        private readonly AppService $appService
    )
    {
    }

    public function getTopPositions(GetAppTopPositionsRequest $request): JsonResponse
    {
        $applicationId = $request->input('applicationId', 1421444);
        $countryId = $request->input('countryId', 1);
        $selectedDate = $request->input('date');

        $app = App::firstOrCreate(['id' => $applicationId]);

        try {
            $appPositions = $this->appService->getSavedAppPositions($app, $selectedDate);
            $appPositions = $this->formatAppPositions($appPositions);

            if (empty($appPositions)) {
                $appPositions = $this->getNewAppPositions($app, $countryId, $selectedDate);

                $this->appService->createAppPositions($app, $appPositions, $selectedDate);
            }
        } catch (Exception $e) {
            return response()->json([
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status_code' => Response::HTTP_OK,
            'message' => 'ok',
            'data' => $appPositions,
        ]);
    }

    /**
     * Get application positions from Apptica.
     *
     * @throws Exception
     */
    private function getNewAppPositions(App $app, int $countryId, string $selectedDate): array
    {
        $response = $this->appticaService->getTopHistory($app->id, $countryId, $selectedDate, $selectedDate);
        $formattedResponse = $this->appticaService->formatResponse($response);

        if (!array_key_exists($selectedDate, $formattedResponse)) {
            throw new Exception('No app data on selected date');
        }

        return $formattedResponse[$selectedDate];
    }

    private function formatAppPositions(Collection $appPositions): array
    {
        $appPositions = $appPositions->toArray();

        return array_combine(
            array_column($appPositions, 'category_id'),
            array_column($appPositions, 'position')
        );
    }
}
