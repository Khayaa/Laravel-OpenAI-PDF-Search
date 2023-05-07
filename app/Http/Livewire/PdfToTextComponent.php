<?php

namespace App\Http\Livewire;

use App\Models\Pdfdoc;
use App\Models\TextData;
use App\Models\TextVector;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use OpenAI\Laravel\Facades\OpenAI;
use Spatie\PdfToText\Pdf;
use Sastrawi\Stemmer\StemmerFactory;

class PdfToTextComponent extends Component
{
    use WithFileUploads;
    public $pdf_doc, $convertedText;
    protected $rules = [
        'pdf_doc' => ['required', 'mimes:pdf']
    ];

    public function getFile()
    {
        $this->validate();
        $file_name = now()->format('YmdHis') . '.' . $this->pdf_doc->getClientOriginalExtension();
        $file = $this->pdf_doc->storePubliclyAs('pdf-file', $file_name, 'public');
       $pdf_file =  Pdfdoc::create([
            'name' => $file_name,
            'file' => $file
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
        //$stemmedTokens = stem($normalizedTokens);

        $this->convertedText = $cleanedText;
        $chunkSize = 4000;

        // split the text into chunks
        $chunks = str_split($cleanedText, $chunkSize);

        // loop through the chunks and store each one as a vector
        foreach ($chunks as $a => $chunk) {
            // generate a vector for the chunk using the OpenAI API
            // $vector = OpenAI::completions()->create([
            //     'model' => 'text-embedding-ada-002',
            //     'prompt' => $chunk,
            // ]);
            //store the chunk to the database
            $text =  TextData::create([
                'file_id'=> $pdf_file->id ,
                'text' => $chunk
            ]);

            // store the vector in the database

            // $vectorModel = new TextVector();
            // $vectorModel->vector = json_encode($vector['data'][0]['embedding']);
            // $vectorModel->text_id = $text->id;
            // $vectorModel->save();
        }

        $this->reset('pdf_doc');
        session()->flash('message', 'File created & converted Successfully');
    }
    public function render()
    {
        return view('livewire.pdf-to-text-component')->extends('layouts.app')->section('content');
    }
}
