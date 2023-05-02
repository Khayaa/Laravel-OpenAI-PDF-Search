<?php

namespace App\Http\Livewire;

use App\Models\Pdfdoc;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Sastrawi\Stemmer\StemmerFactory;
use Spatie\PdfToText\Pdf;

class ConvertFiletoText extends Component
{
    public $document , $convertedText;
    protected $rules = [
        'document'=>'required'
    ];
    public function convertFile(){
        $this->validate();
        $pdf_file = Pdfdoc::find($this->document);

        $sl = str_replace('/', '\\', $pdf_file->file);
        //$pdf_path = url(Storage::url($sl));
        $pdf_path = Storage::path('\public\\'.$sl);
       // dd($pdf_path);
       //$pdf_path = Storage::path($sl);
       //dd(Pdf::getText($pdf_path));
        //$convertedText = Pdf::getText($pdf_path);
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdf_path);
        $this->convertedText = $pdf->getText();
        $tokens = tokenize($this->convertedText);
        $normalizedTokens = normalize_tokens($tokens);
        // Remove stop words from the array
        $stopWords = ['a', 'an', 'the', 'in', 'on', 'at', 'for', 'and', 'but', 'or', 'not', 'with'];
        $filtered_words  = array_diff($normalizedTokens, $stopWords);
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

        $this->convertedText =  $cleanedText;


    }
    private function stem($tokens) {
        $stemmer = new \TextAnalysis\Stemmers\PorterStemmer();

        $result = [];

        foreach ($tokens as $token) {
            if (!empty($token)) {
                $result[] = $stemmer->stem($token);
            }
        }

        return $result;
    }


    public function render()
    {
        $docs = Pdfdoc::all();
        return view('livewire.convert-fileto-text' , ['docs' => $docs])->extends('layouts.app')->section('content');
    }
}
