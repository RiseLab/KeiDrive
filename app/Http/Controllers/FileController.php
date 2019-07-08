<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	$response = [
    		'files' => [],
		    'filesTotal' => 0,
		    'filesPerPage' => 0,
		    'pagesTotal' => 1
	    ];
    	$whereData = [
		    ['id_user', auth()->user()->id]
	    ];
    	$orWhereData = [];
    	if (request('filter')) {
    		$whereData[] = ['title', 'like', '%' . request('filter') . '%'];
    		$orWhereData[] = ['description', 'like', '%' . request('filter') . '%'];
	    }
	    $files = File::where($whereData)
		    ->orWhere($orWhereData)
	        ->orderBy('created_at', 'desc')
	        ->paginate(10);
        foreach ($files->items() as $file) {
        	$response['files'][] = [
        		'id' => $file->id,
		        'title' => $file->title,
		        'description' => $file->description,
		        'created' => $file->created_at,
        		'icon' => (string) \Image::make(storage_path('app/public/icons/' . $file->icon))->encode('data-url'),
		        'state' => 0
	        ];
        }
        $response['filesTotal'] = $files->total();
	    $response['filesPerPage'] = $files->perPage();
	    $response['pagesTotal'] = $files->lastPage();
        return response()->json($response, 200);
    }

    public function download(int $id) {
	    try {
		    $file = File::where('id_user', auth()->user()->id)
			    ->where('id', $id)
			    ->first();
		    if ($file) {
			    return \Image::make(storage_path('app/public/files/' . $file->path))->response();
		    } else {
			    return response()->json(['message' => 'File not found'], 404);
		    }
	    } catch (\Exception $e) {
		    return response()->json(['message' => 'Download error'], 400);
	    }
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
    public function store(Request $request)
    {
        $this->validate($request, [
        	'file' => 'required|mimes:jpg,jpeg,bmp,png,gif',
        	'title' => 'required|max:100',
	        'description' => 'max: 180'
        ]);

	    try {
	    	$tempFolderPath = storage_path('app/public/temp');
	    	$filesFolderPath = storage_path('app/public/files');
		    $iconsFolderPath = storage_path('app/public/icons');
	    	if (!\File::exists($tempFolderPath)) {
	    		\File::makeDirectory($tempFolderPath);
		    }
		    if (!\File::exists($filesFolderPath)) {
			    \File::makeDirectory($filesFolderPath);
		    }
		    if (!\File::exists($iconsFolderPath)) {
			    \File::makeDirectory($iconsFolderPath);
		    }

		    $inputFile = $request->file('file');
		    $fileName = uniqid() . '.' . strtolower($inputFile->getClientOriginalExtension());
		    $inputFile->storeAs('public/temp', $fileName);
		    $inputFile->storeAs('public/temp', '_' . $fileName);
		    $iconPath = $tempFolderPath . '/_' . $fileName;
		    \Image::make($iconPath)->fit(140, 140)->save($iconPath);

		    $file = File::create([
			    'id_user' => auth()->user()->id,
			    'path' => $fileName,
			    'icon' => $fileName,
			    'title' => $request->title,
			    'description' => $request->description
		    ]);
		    $file->save();

		    Storage::disk('public')->move('temp/' . $fileName, 'files/' . $fileName);
		    Storage::disk('public')->move('temp/_' . $fileName, 'icons/' . $fileName);

		    return response()->json($file, 201);
	    } catch (\Exception $e) {
		    return response()->json(['message' => 'Upload error'], 400);
	    }
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
    public function update(Request $request, int $id)
    {
	    $this->validate($request, [
		    'title' => 'required|max:100',
		    'description' => 'max: 180'
	    ]);

	    try {
		    $file = File::where('id_user', auth()->user()->id)
			    ->where('id', $id)
			    ->first();
		    if ($file) {
		    	$file->title = $request->title;
		    	$file->description = $request->description;
		    	$file->save();
			    return response()->json($file, 200);
		    } else {
			    return response()->json(['message' => 'File not found'], 404);
		    }
	    } catch (\Exception $e) {
		    return response()->json(['message' => 'Update error'], 400);
	    }
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\Response
	 */
    public function destroy(int $id)
    {
	    try {
		    $file = File::where('id_user', auth()->user()->id)
			    ->where('id', $id)
			    ->first();
		    if ($file) {
		    	$fileName = $file->path;
		    	Storage::disk('public')->delete([
		    		'files/' . $fileName,
				    'icons/' . $fileName
			    ]);
			    $file->delete();
			    return response()->json(null, 204);
		    } else {
			    return response()->json(['message' => 'File not found'], 404);
		    }
	    } catch (\Exception $e) {
		    return response()->json(['message' => 'Delete error'], 400);
	    }
    }
}
