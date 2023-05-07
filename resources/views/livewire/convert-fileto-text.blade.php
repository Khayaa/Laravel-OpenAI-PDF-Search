<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Convert PDF</h4>
        </div>
        <div class="card-body">
            <form wire:submit.prevent='convertFile' method="post">
                @csrf
                <div class="row mb-3">
                    <div class="col">
                        <div class="form-group">
                            <label for="">User Input</label>
                            <input class="form-control" type="text" name="" id="">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <div class="form-group">

                            <select wire:model='document' class="form-control @error('document') is-invalid @enderror">
                                <option value="">--select file---</option>
                                @foreach ($docs as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                            @error('document')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
                @if (session('message'))
                    <div class="alert alert-success" role="alert">
                        {{ session('message') }}
                    </div>
                @endif
                <div class="text-center mb-3">
                    <button class="btn btn-primary" type="submit">
                        Convert file <span class="me-2">
                            <div wire:loading wire:target='convertFile' class="spinner-border spinner-border-sm"
                                role="status">
                                <span class="visually-hidden"></span>
                            </div>
                        </span>
                    </button>

                </div>
                @if ($convertedText)
                <div class="form-group">
                    <textarea disabled wire:model='convertedText' class="form-control" name="" id="" cols="30" rows="10">

                    </textarea>

                </div>
                @endif
            </form>
        </div>
    </div>
</div>
