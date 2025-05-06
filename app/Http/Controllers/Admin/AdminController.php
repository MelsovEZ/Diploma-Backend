<?php
namespace App\Http\Controllers\Admin;

use App\Http\Requests\Problem\SubmitProblemReportRequest;
use App\Http\Requests\Problem\UpdateProblemReportRequest;
use App\Models\Problem\Problem;
use App\Models\Problem\ProblemPhoto;
use App\Models\Problem\ProblemReport;
use App\Models\Problem\ProblemReportPhoto;
use App\Models\User;
use App\Services\ImageCompressor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    protected $compressor;

    public function __construct(ImageCompressor $compressor)
    {
        $this->compressor = $compressor;
    }
    /**
     * @OA\Post(
     *     path="/api/users/{id}/make-moderator",
     *     summary="Promote user to moderator",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User promoted to moderator",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User promoted to moderator")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=403, description="Admin cannot be changed")
     * )
     */
    public function makeModerator($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status === 'admin') {
            return response()->json(['error' => 'Admin cannot be changed'], 403);
        }

        $user->update(['status' => 'moderator']);
        return response()->json([
            'message' => 'User promoted to moderator',
            'user' => $user]);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/remove-moderator",
     *     summary="Remove moderator role from user",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moderator role removed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Moderator role removed")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=400, description="User is not a moderator")
     * )
     */
    public function removeModerator($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status !== 'moderator') {
            return response()->json(['error' => 'User is not a moderator'], 400);
        }

        $user->update(['status' => 'user']);
        return response()->json([
            'message' => 'Moderator role removed',
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/problems/{problem_id}/assign-moderator/{moderator_id}",
     *     summary="Assign a moderator to a problem and update status to in_progress",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="moderator_id",
     *         in="path",
     *         required=true,
     *         description="ID of the moderator",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moderator assigned and problem status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Moderator assigned to the problem and status changed to in_progress")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Problem or moderator not found"),
     *     @OA\Response(response=400, description="This moderator is already assigned to the problem")
     * )
     */

    public function assignModeratorToProblem($problem_id, $moderator_id): JsonResponse
    {
        // Находим проблему по ID
        $problem = Problem::find($problem_id);
        if (!$problem) {
            return response()->json(['error' => 'Problem not found'], 404);
        }

        // Находим модератора по ID
        $moderator = User::find($moderator_id);
        if (!$moderator || $moderator->status !== 'moderator') {
            return response()->json(['error' => 'Moderator not found or invalid role'], 404);
        }

        // Проверяем, существует ли уже отчёт для этой проблемы
        $existingReport = ProblemReport::where('problem_id', $problem_id)->first();

        if ($existingReport) {
            // Если модератор тот же, ничего не меняем
            if ($existingReport->moderator_id === $moderator->id) {
                return response()->json(['message' => 'This moderator is already assigned to the problem'], 400);
            }

            // Если модератор другой, обновляем данные
            $existingReport->moderator_id = $moderator->id;
            $existingReport->assigned_at = now(); // Обновляем время назначения
            $existingReport->status = 'in_progress'; // Статус можно обновить, если нужно
            $existingReport->save();
        } else {
            // Если отчёта нет, создаём новый
            ProblemReport::create([
                'problem_id'    => $problem->problem_id,
                'moderator_id'  => $moderator->id,
                'description'   => null,
                'assigned_at'   => now(),
                'submitted_at'  => null,
                'confirmed_at'  => null,
                'status'        => 'in_progress',
            ]);
        }

        // Обновляем саму проблему
        $problem->moderator_id = $moderator->id;
        $problem->status = 'in_progress';  // Статус задачи меняем на in_progress
        $problem->assigned_at = now();
        $problem->save();

        return response()->json(['message' => 'Moderator assigned to the problem and status changed to in_progress']);
    }


    /**
     * @OA\Post(
     *     path="/api/problems/{problem_id}/report",
     *     summary="Submit a problem report with description and photos",
     *     tags={"Moderator"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"description", "photos[]"},
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="We cleaned up the area and removed all trash"
     *                 ),
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Attach 1 to 5 photos"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Problem report submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Problem report submitted, photos added, status updated to in_review")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Problem is not in progress or already submitted"),
     *     @OA\Response(response=403, description="Unauthorized, only moderators can submit reports"),
     *     @OA\Response(response=404, description="Problem or report not found")
     * )
     */


    public function submitProblemReport($problem_id, SubmitProblemReportRequest $request): JsonResponse
    {
        $problem = Problem::find($problem_id);
        if (!$problem) {
            return response()->json(['error' => 'Problem not found'], 404);
        }

        if ($problem->status !== 'in_progress') {
            return response()->json(['error' => 'Problem is not in progress'], 400);
        }

        $moderator = auth()->user();
        if (!$moderator || $moderator->status !== 'moderator') {
            return response()->json(['error' => 'Unauthorized, only moderators can submit reports'], 403);
        }

        $problemReport = ProblemReport::where('problem_id', $problem->problem_id)
            ->where('moderator_id', $moderator->id)
            ->first();

        if (!$problemReport) {
            return response()->json(['error' => 'Report not found'], 404);
        }

        if ($problemReport->status === 'in_review') {
            return response()->json(['error' => 'This report has already been submitted and is in review'], 400);
        }


        $problemReport->update([
            'description'   => $request->description,
            'submitted_at'  => now(),
            'status'        => 'in_review'
        ]);

        $photoUrls = [];
        foreach ($request->file('photos') as $photo) {
            if ($photo->isValid()) {
                $userId = auth()->id();
                $problemId = $problem->problem_id;

                $path = Storage::disk('s3')->put("problems_solution/Moderator_{$userId}/problem_{$problemId}", $photo, 'public');
                $photoUrl = Storage::disk('s3')->url($path);

                DB::table('problem_report_photos')->insert([
                    'report_id' => $problemReport->report_id,
                    'photo_url' => $photoUrl,
                ]);
                $photoUrls[] = $photoUrl;
            }
        }

        $problem->status = 'in_review';
        $problem->save();

        return response()->json([
            'message' => 'Problem report submitted, photos added, status updated to in_review',
            'report' => [
                'description' => $problemReport->description,
                'photos' => $photoUrls
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/problems/{problem_id}/report/update",
     *     summary="Update a problem report with new description and photos",
     *     tags={"Moderator"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="Updated report: removed graffiti and cleaned the wall"
     *                 ),
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="New set of photos (1 to 5). Replaces old ones"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Problem report updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Problem report updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Problem is not in review"),
     *     @OA\Response(response=403, description="Unauthorized, only moderators can update reports"),
     *     @OA\Response(response=404, description="Problem or report not found")
     * )
     */


    public function updateProblemReport($problem_id, UpdateProblemReportRequest $request): JsonResponse
    {
        $problem = Problem::find($problem_id);
        if (!$problem) {
            return response()->json(['error' => 'Problem not found'], 404);
        }

        if ($problem->status !== 'in_review') {
            return response()->json(['error' => 'Problem is not in review'], 400);
        }

        $moderator = auth()->user();
        if (!$moderator || $moderator->status !== 'moderator') {
            return response()->json(['error' => 'Unauthorized, only moderators can submit reports'], 403);
        }

        $problemReport = ProblemReport::where('problem_id', $problem->problem_id)
            ->where('moderator_id', $moderator->id)
            ->first();

        if (!$problemReport) {
            return response()->json(['error' => 'Report not found'], 404);
        }

        $problemReport->update([
            'description'   => $request->description,
        ]);

        $photoUrls = [];
        if ($request->hasFile('photos')) {
            $this->deleteProblemReportPhotos($problemReport);

            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $compressedImage = $this->compressor->compress($photo);

                    $tempFile = tempnam(sys_get_temp_dir(), 'compressed_');
                    file_put_contents($tempFile, $compressedImage);

                    $userId = auth()->id();
                    $problemId = $problem->problem_id;

                    $path = Storage::disk('s3')->putFile("problems_solution/Moderator_{$userId}/problem_{$problemId}", new \Illuminate\Http\File($tempFile));
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $photoUrl = Storage::disk('s3')->url($path);

                    // Сохраняем ссылку на фото в таблицу problem_report_photos
                    ProblemReportPhoto::create([
                        'report_id' => $problemReport->report_id,
                        'photo_url' => $photoUrl,
                    ]);
                    $photoUrls[] = $photoUrl;
                    unlink($tempFile);
                }
            }
        } else {
            $photoUrls = ProblemReportPhoto::where('report_id', $problemReport->report_id)->pluck('photo_url')->toArray();
        }


        return response()->json([
            'message' => 'Problem report updated successfully',
            'report' => [
                'description' => $problemReport->description,
                'photos' => $photoUrls
            ]
        ]);

    }

    /**
     * @OA\Post(
     *     path="/api/problems/{problem_id}/admin-review",
     *     summary="Административная проверка решения проблемы модератором",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         description="ID проблемы",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"status", "comment"},
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     enum={"done", "declined", "in_review"},
     *                     description="Статус проблемы после проверки администратором (done, declined, in_review)"
     *                 ),
     *                 @OA\Property(
     *                     property="comment",
     *                     type="string",
     *                     nullable=true,
     *                     description="Комментарий администратора к проверке проблемы"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Проблема успешно проверена",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     example="Статус проблемы изменен на done"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Проблема не найдена"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Неавторизованный доступ, только администраторы могут проверять проблемы"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверный статус проблемы или проблема не находится на проверке"
     *     )
     * )
     */

    public function reviewProblem($problem_id, Request $request): JsonResponse
    {
        $problem = Problem::find($problem_id);
        if (!$problem) {
            return response()->json(['error' => 'Problem not found'], 404);
        }

        $admin = auth()->user();
        if (!$admin || $admin->status !== 'admin') {
            return response()->json(['error' => 'Неавторизованный доступ, только администраторы могут проверять проблемы'], 403);
        }

        if (!in_array($problem->status, ['pending', 'in_progress', 'in_review', 'done', 'declined'])) {
            return response()->json(['error' => 'Статус проблемы не позволяет ее редактировать'], 400);
        }

        $oldStatus = $problem->status;
        $status = $request->input('status');
        $comment = $request->input('comment');

        $responseMessage = [];

        if ($status && !in_array($status, ['done', 'declined', 'in_review'])) {
            return response()->json(['error' => 'Неверный статус'], 400);
        }
        if ($status) {
            $problem->status = $status;
            $problem->save();
            $responseMessage[] = 'Статус проблемы изменен на ' . $status;

            // Обновляем статус в problem_reports
            DB::table('problem_reports')
                ->where('problem_id', $problem_id)
                ->update(['status' => $status]);
            $responseMessage[] = 'Статус отчета обновлен на ' . $status;

            // Обновляем confirmed_at в зависимости от нового статуса
            if ($status === 'done') {
                DB::table('problem_reports')
                    ->where('problem_id', $problem_id)
                    ->update(['confirmed_at' => now()]);
                $responseMessage[] = 'Дата подтверждения отчета обновлена';
            } else {
                DB::table('problem_reports')
                    ->where('problem_id', $problem_id)
                    ->update(['confirmed_at' => null]);
                $responseMessage[] = 'Дата подтверждения отчета сброшена';
            }
        }

        if ($comment !== null) {
            $currentComment = DB::table('problem_reports')
                ->where('problem_id', $problem_id)
                ->value('admin_comment');
            if ($comment !== $currentComment || $comment === '') {
                // Сохраняем пустой комментарий как null
                $updatedComment = $comment === '' ? null : $comment;

                DB::table('problem_reports')
                    ->where('problem_id', $problem_id)
                    ->update(['admin_comment' => $updatedComment]);
                DB::table('problems')
                    ->where('problem_id', $problem_id)
                    ->update(['admin_comment' => $updatedComment]);

                $responseMessage[] = 'Комментарий обновлен'; // Добавляем сообщение о комментарии
            }
        }

        if (empty($responseMessage)) {
            return response()->json(['message' => 'Нет изменений для обновления']);
        }
        return response()->json([
            'messages' => $responseMessage
        ]);
    }


    public function deleteProblemReportPhotos(ProblemReport $problemReport): void
    {
        // Удаляем все фотографии из базы и с сервера
        $photos = ProblemReportPhoto::where('report_id', $problemReport->report_id)->get();
        foreach ($photos as $photo) {
            $filePath = ltrim(parse_url($photo->photo_url, PHP_URL_PATH), '/');
            $filePath = str_replace("diploma-bucket/", "", $filePath);

            // Проверяем существование файла в хранилище
            if (Storage::disk('s3')->exists($filePath)) {
                // Удаляем файл из хранилища
                Storage::disk('s3')->delete($filePath);
            }

            // Удаляем запись о фото в базе
            $photo->delete();
        }
    }


}
