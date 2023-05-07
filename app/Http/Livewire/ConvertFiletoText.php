<?php

namespace App\Http\Livewire;

use App\Models\Pdfdoc;
use App\Models\TextData;
use App\Models\TextVector;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use OpenAI\Laravel\Facades\OpenAI;
use Sastrawi\Stemmer\StemmerFactory;

class ConvertFiletoText extends Component
{
    public $document, $convertedText;
    protected $rules = [
        'document' => 'required',
    ];
    public function convertFile()
    {
        $this->validate();
        $pdf_file = Pdfdoc::find($this->document);
        

    }
    private function stem($tokens)
    {
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
        return view('livewire.convert-fileto-text', ['docs' => $docs])->extends('layouts.app')->section('content');
    }
}
