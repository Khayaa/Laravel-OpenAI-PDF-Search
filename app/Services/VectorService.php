<?php

namespace App\Services;

use App\Models\TextData;
use App\Models\TextVector;
use App\Models\Vector;
use Illuminate\Support\Facades\DB;

class VectorService
{
    /**
     * Retrieve vectors for a given text.
     *
     * @param string $text
     * @return array
     */
    public function getVectorsForText(string $text): array
    {
        $vectors = DB::table('vectors')
            ->select('id', 'vector')
            ->where('text_id', '=', DB::raw("(SELECT id FROM texts WHERE text = '$text')"))
            ->get()
            ->toArray();

        return array_map(function($vector) {
            return [
                'id' => $vector->id,
                'vector' => json_decode($vector->vector, true)
            ];
        }, $vectors);
    }

    public function getTextsFromIds(array $ids): array
{
    $texts = TextData::whereIn('id', $ids)->get()->toArray();
    $textsById = [];

    foreach ($texts as $text) {
        $textsById[$text['id']] = $text['text'];
    }

    $textsOrderedByIds = [];

    foreach ($ids as $id) {
        if (isset($textsById[$id])) {
            $textsOrderedByIds[] = $textsById[$id];
        }
    }

    return $textsOrderedByIds;
}


    /**
     * Retrieve the text for a given vector ID.
     *
     * @param int $vectorId
     * @return string|null
     */
    public function getTextForVector(int $vectorId): ?string
    {
        $text = DB::table('texts')
            ->select('text')
            ->where('id', '=', DB::raw("(SELECT text_id FROM vectors WHERE id = $vectorId)"))
            ->first();

        return $text ? $text->text : null;
    }

    /**
     * Retrieve the most similar vectors for a given vector.
     *
     * @param array $vector
     * @param int $limit
     * @return array
     */
    public function getMostSimilarVectors(array $vector,$file_id , int $limit = 10): array
    {
        $vectors = TextVector::where('file_id' , $file_id)->get()
        ->map(function($vector) {
                return [
                    'id' => $vector->id,
                    'text_id'=> $vector->text_id ,
                    'vector' => json_decode($vector->vector, true)
                ];
            })
            ->toArray();

        $similarVectors = [];
        foreach ($vectors as $v) {
            $cosineSimilarity = $this->calculateCosineSimilarity($vector, $v['vector']);
            $similarVectors[] = [
                'id' => $v['id'],
                'similarity' => $cosineSimilarity
            ];
        }

        usort($similarVectors, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($similarVectors, 0, $limit);
    }

    /**
     * Calculate the cosine similarity between two vectors.
     *
     * @param array $v1
     * @param array $v2
     * @return float
     */
    private function calculateCosineSimilarity(array $v1, array $v2): float
    {
        $dotProduct = 0;
        $v1Norm = 0;
        $v2Norm = 0;

        foreach ($v1 as $i => $value) {
            $dotProduct += $value * $v2[$i];
            $v1Norm += $value * $value;
            $v2Norm += $v2[$i] * $v2[$i];
        }

        $v1Norm = sqrt($v1Norm);
        $v2Norm = sqrt($v2Norm);

        return $dotProduct / ($v1Norm * $v2Norm);
    }
}
