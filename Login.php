<?php
require_once("Public/Header.php");
$CALLBACK_DATA = array( "Code" => 0 );
/*
|--------------------------------------------------------------------------
| Send SMS
|--------------------------------------------------------------------------
| Norm: 2017/05/03
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_POST['Action_Type'] == 'SendSMS' && $_POST['Tel'] != '' )
{	# @ Send SMS
	$Bol_Send_Result = sendCodeBySMSJson( $_POST['Tel'] );
	if( isset( $_SESSION['sms']['sendcode'] ) == true && $Bol_Send_Result == true ){ $CALLBACK_DATA['Code'] = 1; }
}
/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
| Norm: 2017/05/03
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Check_Login' )
{
	if( isset( $_SESSION['Login_Info'] ) == true )
	{
		$CALLBACK_DATA['Login_Info'] = $_SESSION['Login_Info'];
		$CALLBACK_DATA['Code'] = 1;
	}
	if( isset( $_SESSION['Login_Info'] ) == false && isset( $_COOKIE["Login_Cache"] ) == true )
	{
		$Str_Login_ID = base64_decode( $_COOKIE["Login_Cache"] );
		$Arr_Seller_Info = listCoreBySql( " select ID,xsxm as Name,shouji as Mobile,lpbh as BuildNum from t_yingxiaoyuan where ID = '" . $Str_Login_ID . "' limit 0,1" );
		if( is_array( $Arr_Seller_Info ) )
		{	# @ Send Call Back Data
			$_SESSION['Login_Info'] = $Arr_Seller_Info[0];
			$CALLBACK_DATA['Login_Info'] = $Arr_Seller_Info[0];
			$CALLBACK_DATA['Code'] = 1;
		}	
	}
}	
/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
| Norm: 2017/05/03
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_POST['Action_Type'] == 'Login' && $_POST['Login_Info'] != '' )
{	# @	Check Login
	$Arr_Login_Info = json_decode( $_POST['Login_Info'], true );
	if( $Arr_Login_Info['Login_Account'] != '' && $Arr_Login_Info['Login_Password'] != '' )
	{	
		$Arr_Seller_Info = listCoreBySql( " select ID,xsxm as Name,shouji as Mobile,lpbh as BuildNum,( select g_name from t_loupan where t_loupan.ID = t_yingxiaoyuan.lpbh limit 0,1 ) as BuildName from t_yingxiaoyuan where shouji = '" . $Arr_Login_Info['Login_Account'] . "' and mima = '" . $Arr_Login_Info['Login_Password'] . "' limit 0,1" );
		if( is_array( $Arr_Seller_Info ) )
		{	# @ Send Call Back Data
			$_SESSION['Login_Info'] = $Arr_Seller_Info[0];
			$CALLBACK_DATA['Login_Info'] = $Arr_Seller_Info[0];
			$CALLBACK_DATA['Code'] = 1;
			# @ Set Cache
			setcookie( "Login_Cache", base64_encode( $Arr_Seller_Info[0]['ID'] ), time() + 86400 );
		}
	}
}
if( $_GET['Data_Type'] == 'JP' ){ die( "$callback(" . json_encode( $CALLBACK_DATA ) . ")" ); }else{ die( json_encode( $CALLBACK_DATA ) ); }	
?>