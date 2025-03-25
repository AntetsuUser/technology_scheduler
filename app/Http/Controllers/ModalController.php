<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
class ModalController extends Controller
{
    public function modal()
    {
        return view('modal');
    }
}