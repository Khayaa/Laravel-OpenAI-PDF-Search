<?php

namespace App\Http\Livewire;

use App\Models\Pdfdoc;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\PdfToText\Pdf;

class PdfToTextComponent extends Component
{
    use WithFileUploads;
    public $pdf_doc;
    protected $rules = [
        'pdf_doc'=>['required', 'mimes:pdf']
    ];

    public function getFile(){
        $this->validate();
        $file_name = now()->format('YmdHis') . '.' . $this->pdf_doc->getClientOriginalExtension();
        $file = $this->pdf_doc->storePubliclyAs('pdf-file', $file_name ,'public');
        Pdfdoc::create([
            'name'=> $file_name ,
            'file' => $file
        ]);
        $this->reset('pdf_doc');
        session()->flash('message' , 'File created Successfully');

    }
    public function render()
    {
        return view('livewire.pdf-to-text-component')->extends('layouts.app')->section('content');
    }
}
