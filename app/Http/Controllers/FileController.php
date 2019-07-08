<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	$response = [];
        $files = File::where('id_user', auth()->user()->id)
	        ->paginate(10);
        foreach ($files->items() as $file) {
        	$response[] = [
        		'id' => $file->id,
		        'title' => $file->title,
		        'description' => $file->description,
		        'created' => $file->created_at,
        		'icon' => (string) \Image::make(storage_path('app/public/icons/' . $file->icon))->encode('data-url')
	        ];
        }
        return response()->json($response, 200);
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

	    if ($request->hasFile('file')) {
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
		    \Image::make($iconPath)->fit(120, 120)->save($iconPath);

		    $file = File::create([
			    'id_user' => auth()->user()->id,
			    'path' => $fileName,
			    'icon' => $fileName,
			    'title' => $request->title,
			    'description' => $request->description
		    ]);
		    $file->save();

		    \Storage::disk('public')->move('temp/' . $fileName, 'files/' . $fileName);
		    \Storage::disk('public')->move('temp/_' . $fileName, 'icons/' . $fileName);
	    } else {
		    return response()->json(['message' => 'Upload error'], 400);
	    }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
