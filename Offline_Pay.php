<?php
require_once("Public/Header.php");
$CALLBACK_DATA = array( "Code" => 0 );
/*
|--------------------------------------------------------------------------
| Offline Pay
|--------------------------------------------------------------------------
| Norm: 2017/05/16
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_POST['Action_Type'] == 'Offline_Pay' && $_POST['Pay_Data'] != '' )
{	# @ Get Data
	$Arr_Pay_Data = $_POST['Pay_Data'];
	# @ Insert Pay Person
	updateCoreBySql( "update t_dingdanxitong set fukuanren='".$Arr_Pay_Data['Pay_Person']."', fksjc='".time()."', gshfksj='".date( " Y-m-d H:i:s " )."',zffs=2,ddzt=1 where ID='".$Arr_Pay_Data['FK']."'" );
	# @ Insert Payment Voucher Data
	//die(print_r($Arr_Pay_Data['Payment_Voucher']));
	if(is_array($Arr_Pay_Data['Payment_Voucher']))
	{	
		foreach($Arr_Pay_Data['Payment_Voucher'] as $key=>$value){			
			$arrPic=array('FK'=>$Arr_Pay_Data['FK'],'tupian'=>$value);
			submitCore( 't_dingdanxitong_2', $arrPic);
		}
	}
	# @ Insert Receipt Data
	if(is_array($Arr_Pay_Data['Receipt']))
	{
		foreach($Arr_Pay_Data['Receipt'] as $key=>$value){
			$arrPic=array('FK'=>$Arr_Pay_Data['FK'],'tupian'=>$value);
			submitCore( 't_dingdanxitong_3', $arrPic);
		}
		
	}
	# @ Return Code
	$CALLBACK_DATA['Code'] = 1;
}
if( $_GET['Data_Type'] == 'JP' ){ die( "$callback(" . json_encode( $CALLBACK_DATA ) . ")" ); }else{ die( json_encode( $CALLBACK_DATA ) ); }	
?>
