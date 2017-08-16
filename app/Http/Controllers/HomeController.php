<?php
/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/1/27
 * Time: 上午9:19
 */

namespace App\Http\Controllers;


class HomeController extends Controller
{
    public function index()
    {
		header("Content-type: text/html; charset=utf-8"); 
        echo "此目录没用! 没有/home 这个目录";
        exit;
    }

}