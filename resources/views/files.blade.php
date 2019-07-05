@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-header">Add files</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="fileTitle">Title</label>
                            <input type="text" class="form-control" id="fileTitle" placeholder="Enter file title">
                        </div>
                        <div class="form-group">
                            <label for="fileDescription">Description</label>
                            <textarea class="form-control" id="fileDescription" rows="4" placeholder="Enter file description"></textarea>
                        </div>
                        <div class="form-group">
                            <input type="file" class="form-control-file" id="fileRaw" multiple>
                        </div>
                        <button class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">Files</div>
                    <div class="card-body">
                        Files here.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
