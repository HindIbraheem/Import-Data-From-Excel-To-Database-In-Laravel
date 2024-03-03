<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class ImportController extends Controller
{
    public function import(Request $request){
        $file = $request->file('excel_file');
        $ExcelData = Excel::toArray([],$file);
        $counter = 0; //$counter is used to ignore the header row from the excel sheet
        //reads the row one by one form the excel sheet

        // $drawing = new Drawing();
        // $drawing->setName('Logo');
        // $drawing->setDescription('This is my logo');
        // $drawing->setPath(public_path('/img/logo.jpg'));
        // $drawing->setHeight(90);
        // $drawing->setCoordinates('B3');

        // return $drawing;


        $spreadsheet = IOFactory::load(request()->file('excel_file'));
        $i = 0;
        foreach ($spreadsheet->getActiveSheet()->getDrawingCollection() as $drawing) {
            if ($drawing instanceof MemoryDrawing) {
                ob_start();
                call_user_func(
                    $drawing->getRenderingFunction(),
                    $drawing->getImageResource()
                );
                $imageContents = ob_get_contents();
                ob_end_clean();
                switch ($drawing->getMimeType()) {
                    case MemoryDrawing::MIMETYPE_PNG :
                        $extension = 'png';
                        break;
                    case MemoryDrawing::MIMETYPE_GIF:
                        $extension = 'gif';
                        break;
                    case MemoryDrawing::MIMETYPE_JPEG :
                        $extension = 'jpg';
                        break;
                }
            } else {
                $zipReader = fopen($drawing->getPath(), 'r');
                $imageContents = '';
                while (!feof($zipReader)) {
                    $imageContents .= fread($zipReader, 1024);
                }
                fclose($zipReader);
                $extension = $drawing->getExtension();
            }

            $myFileName = time() .++$i. '.' . $extension;
            file_put_contents('./assets/images/product/' . $myFileName, $imageContents);
        }







        foreach ($ExcelData as $row){
            //reads the columns from each row of excel sheet each column's data can be accessed using index starting from 0
            //column1 -> $data[0]; and so on.
            foreach ($row as $data){

                print_r($data[1]);
                if($counter != 0) {
                    // $user = new User;
                    // $user->first_name = $data[0];
                    // $user->user_name = $data[1];
                    // $user->password = Hash::make($data[3]);
                    // $user->save();


                    $contacts = new Contact;
                    $contacts->user_id = 1;
                    $contacts->email = $data[2];
                    $contacts->contact = $myFileName;
                    $contacts->save();





                }
                else{
                    $counter++;
                }
            }
        }
        // return "Success : Your Excel Sheet Data Is Uploaded Successfully In The Database";
    }
}
