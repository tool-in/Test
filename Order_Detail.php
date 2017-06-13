<?php
require_once("Public/Header.php");
$CALLBACK_DATA = array( "Code" => 0 );
/*
|--------------------------------------------------------------------------
| Order Detail
|--------------------------------------------------------------------------
| Norm: 2017/05/09
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Order_Detail' && $_GET['Order_ID'] != '' )
{	# @ Plan Data
	$Arr_Plan_Data = listCoreBySql( " select ID as Plan_ID,zongjine as Finall_Price,famc as Plan_Name,fajcj as Plan_Basic_Price,fayhj as Plan_Discounted_Price,fasltdz as Plan_Thum_Pic_Url,zongjine as Total_Amount from t_dingdanxitong  where ID = '" . $_GET['Order_ID'] . "'" );
	$CALLBACK_DATA['Plan_Data'] = $Arr_Plan_Data[0];
	# @ Finall Price
	$CALLBACK_DATA['Total_Amount']  = $Arr_Plan_Data[0]['Total_Amount'];
	# @ Value Added Pack
	$CALLBACK_DATA['Value_Added_Pack_Data'] = listCoreBySql( " select zzbbh as Pack_ID,zzbmc as Pack_Name,xgsl as Pack_Quantity,zzbjcj as Pack_Basic_Price,zzbyhj as Pack_Discounted_Price,zzbsltdz as Pack_Thum_Pic_Url from t_dingdanxitong_1 where FK = '" . $_GET['Order_ID'] . "'" );
	# @ Return Code
	$CALLBACK_DATA['Code'] = 1;
	unset( $CALLBACK_DATA['Plan_Data']['Total_Amount'] );
}
/*
|--------------------------------------------------------------------------
| Pay Order
|--------------------------------------------------------------------------
| Norm: 2017/05/09
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_POST['Action_Type'] == 'Pay_Order' && $_POST['Order_ID'] != '' && $_POST['Pay_Type'] != '' )
{	# @ Check ID
	$Bol_Check_Order_ID_Res = CheckId( $_POST['Order_ID'] );
	$Bol_Check_Pay_Type_Res = CheckId( $_POST['Pay_Type'] );
	if( $Bol_Check_Order_ID_Res && $Bol_Check_Pay_Type_Res )
	{	# @ Update Pay Type 
		updateCoreBySql( " UPDATE t_dingdanxitong SET zffs = " . $_POST['Pay_Type'] . " WHERE ID = '" . $_POST['Order_ID'] . "'" );
		# @ Return Code
		$CALLBACK_DATA['Code'] = 1;
	}	
}
/*
|--------------------------------------------------------------------------
| Pay
|--------------------------------------------------------------------------
| Norm: 2017/05/17
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Pay' && $_GET['Order_ID'] != '' )
{	# @ Check ID
	$Bol_Check_Order_ID_Res = CheckId( $_GET['Order_ID'] );
	
	if( $Bol_Check_Order_ID_Res )
	{	
		$arrOneOrder=detailCore($_GET['Order_ID'],"t_dingdanxitong","ID,zffs,zongjine");
		$_GET['id'] = $_GET['Order_ID'];
		$_GET['t'] = "t_dingdanxitong";
		$CALLBACK_DATA['ID']=$arrOneOrder['ID'];
		$CALLBACK_DATA['zongjine']=$arrOneOrder['zongjine'];
		$CALLBACK_DATA['zffs']=$arrOneOrder['zffs'];
		if($arrOneOrder['zffs'] == 0){
			$_GET['type'] = "wxpayC2B";
		}elseif($arrOneOrder['zffs'] == 1){
			$_GET['type'] = "alipayC2B";
		}
		if($_GET['type']!=''){
			require_once("../public/pay.php");
			$CALLBACK_DATA['Code'] = $arrReturn['status'];
			$CALLBACK_DATA['qr_code'] = $arrReturn['qr_code'];
		}else{
			$CALLBACK_DATA['Code']=0;
			$CALLBACK_DATA['msg']='选择支付方式';
		}
		
	}
}
/*
|--------------------------------------------------------------------------
| Check Order
|--------------------------------------------------------------------------
| Norm: 2017/05/17
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Check_Order' && $_GET['Order_ID'] != '' )
{	# @ Check ID
	$Bol_Check_Order_ID_Res = CheckId( $_GET['Order_ID'] );
	
	if( $Bol_Check_Order_ID_Res )
	{	
		$arrOneOrder=detailCore($_GET['Order_ID'],"t_dingdanxitong","ID,ddzt");
		if($arrOneOrder['ddzt'] == 1){
			$CALLBACK_DATA['Code']=1;
		}
	}
}
/*
|--------------------------------------------------------------------------
| Pay Success
|--------------------------------------------------------------------------
| Norm: 2017/05/17
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Pay_Success' && $_GET['Order_ID'] != '' )
{	# @ Check ID
	$Bol_Check_Order_ID_Res = CheckId( $_GET['Order_ID'] );
	
	if( $Bol_Check_Order_ID_Res )
	{	
		$arrOneOrder=detailCore($_GET['Order_ID'],"t_dingdanxitong","ID,zffs,round(zongjine,2) zongjine,(select ifnull(xingming,'') from t_loupanyezhu where ID=yzbh) as khxm,ddzt");
		$arrOneOrder['Payment_Voucher']=listCoreByWhere("t_dingdanxitong_2","FK='".$_GET['Order_ID']."'","","tupian");
		$arrOneOrder['Receipt']=listCoreByWhere("t_dingdanxitong_3","FK='".$_GET['Order_ID']."'","","tupian");
		if($arrOneOrder['ddzt'] == 1){
			$CALLBACK_DATA=$arrOneOrder;
			$CALLBACK_DATA['Code']=1;
		}else{
			$CALLBACK_DATA=$arrOneOrder;
			$CALLBACK_DATA['msg']='请先付款!';
		}
	}
}
if( $_GET['Data_Type'] == 'JP' ){ die( "$callback(" . json_encode( $CALLBACK_DATA ) . ")" ); }else{ die( json_encode( $CALLBACK_DATA ) ); }	
?>
