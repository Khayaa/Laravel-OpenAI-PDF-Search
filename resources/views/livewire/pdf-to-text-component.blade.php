<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>UPLOAD PDF</h4>
        </div>
        <div class="card-body">
            <form wire:submit.prevent='getFile' method="post">
                @csrf
                <div class="row mb-3">
                    <div class="col">
                        <div class="form-group">
                            <input wire:model='pdf_doc' class="form-control @error('pdf_doc') is-invalid @enderror"
                                type="file" name="pdf_doc" id="pdf_doc">
                            @error('pdf_doc')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
                @if ($convertedText)
                <div class="row mb-3">
                    <div class="col">
                        <label for="convertedText">Converted Text</label>
                        <div class="form-group">
                            <textarea disabled wire:model='convertedText' class="form-control" name="" id="" cols="30"
                                rows="10">
                            </textarea>

                        </div>

                    </div>
                </div>
                @endif
                @if (session('message'))
                <div class="alert alert-success" role="alert">
                    {{ session('message') }}
                </div>
                @endif
                <div class="text-center mb-3">
                    <button class="btn btn-primary" type="submit">
                        Upload & Convert file <span class="me-2">
                            <div wire:loading wire:target='getFile' class="spinner-border spinner-border-sm"
                                role="status">
                                <span class="visually-hidden"></span>
                            </div>
                        </span>
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>
