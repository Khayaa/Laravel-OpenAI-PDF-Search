<?php

namespace App\Http\Livewire;

use App\Models\Pdfdoc;
use App\Models\TextData;
use App\Models\TextVector;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use OpenAI\Laravel\Facades\OpenAI;
use Sastrawi\Stemmer\StemmerFactory;

class PdfToTextComponent extends Component
{
    use WithFileUploads;
    public $pdf_doc, $convertedText;
    protected $rules = [
        'pdf_doc' => ['required', 'mimes:pdf'],
    ];

    public function getFile()
    {
        $this->validate();
        $file_name = now()->format('YmdHis') . '.' . $this->pdf_doc->getClientOriginalExtension();
        $file = $this->pdf_doc->storePubliclyAs('pdf-file', $file_name, 'public');
        $pdf_file = Pdfdoc::create([
            'name' => $file_name,
            'file' => $file,
        ]);
        //Convert Pdf to Text
        $sl = str_replace('/', '\\', $file);
        $pdf_path = Storage::path('\public\\' . $sl);
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdf_path);
        $pdf_text = $pdf->getText();
        //Tokenizing Text
        $tokens = tokenize($pdf_text);
        //Normalizing Text
        $normalizedTokens = normalize_tokens($tokens);
        // Remove stop words from the array
        $stopWords = ['a', 'an', 'the', 'in', 'on', 'at', 'for', 'and', 'but', 'or', 'not', 'with'];
        $filtered_words = array_diff($normalizedTokens, $stopWords);
        // create stemmer instance
        $stemmerFactory = new StemmerFactory();
        $stemmer = $stemmerFactory->createStemmer();
        // assuming $normalizedTokens contains an array of normalized text tokens
        $stemmedTokens = array();
        foreach ($filtered_words as $token) {
            // stem each token and add it to $stemmedTokens array
            $stemmedTokens[] = $stemmer->stem($token);
        }
        $cleanedText = implode(' ', $stemmedTokens);

        $this->convertedText = $cleanedText;
        // $chunkSize = 100;

        // // split the text into chunks
        // $chunks = str_split($cleanedText, $chunkSize);

        // Split text into chunks with context
        // $wordsPerChunk = 1000; // number of words per chunk
        // $pattern = '/\s+/' . '{1,' . $wordsPerChunk . '}(?=\s)/'; // regex pattern to split by words
        // $chunks = preg_split($pattern, $cleanedText, -1, PREG_SPLIT_NO_EMPTY);

        // Split text into chunks with context
        $wordsPerChunk = 1000; // number of words per chunk
        $overlapWords = 200; // number of overlapping words between chunks
        $pattern = '/\s+' . '\{1,' . ($wordsPerChunk + $overlapWords) . '\}(?=\s)/'; // regex pattern to split by words with overlap

        $chunks = preg_split($pattern, $cleanedText, -1, PREG_SPLIT_NO_EMPTY);
        $chunkCount = count($chunks);

        // Loop through the chunks and store each one as a vector
        foreach ($chunks as $a => $chunk) {
            $context = '';
            if ($a > 0) {
                $prevChunk = $chunks[$a - 1];
                $context = implode(' ', array_slice(explode(' ', $prevChunk), -$overlapWords));
            }
            if ($a < $chunkCount - 1) {
                $nextChunk = $chunks[$a + 1];
                $context .= ' ' . implode(' ', array_slice(explode(' ', $nextChunk), 0, $overlapWords));
            }

            $chunkWithContext = $context . ' ' . $chunk;

            // Generate a vector for the chunk using the OpenAI API
            try {
                $vector = OpenAI::embeddings()->create([
                    'model' => 'text-embedding-ada-002',
                    'input' => $chunkWithContext,
                ]);

                // Store the chunk to the database
                $textData = TextData::firstOrCreate([
                    'file_id' => $pdf_file->id,
                    'text' => $chunkWithContext,
                ]);

                // Store the vector in the database
                $vectors = TextVector::create([
                    'text_id' => $textData->id,
                    'vector' => json_encode($vector['data'][0]['embedding']),
                    'file_id' => $pdf_file->id,
                ]);
            } catch (\Exception $e) {
                // Handle other exceptions
                dd('OpenAI API exception: ' . $e->getMessage());
                continue;
            }
        }



        $this->reset('pdf_doc');
        session()->flash('message', 'File created & converted Successfully');
    }
    public function render()
    {
        return view('livewire.pdf-to-text-component')->extends('layouts.app')->section('content');
    }
}
