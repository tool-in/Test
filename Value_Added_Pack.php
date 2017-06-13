<?php
require_once("Public/Header.php");
$CALLBACK_DATA = array( "Code" => 0 );
/*
|--------------------------------------------------------------------------
| Value_Added_Pack List
|--------------------------------------------------------------------------
| Norm: 2017/05/08
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Value_Added_Pack' && $_GET['Plan_ID'] != '' )
{	# @ Plan List
	$Arr_Plan_List = listCoreByWhere( "t_yangbanfang", " ID = '" . $_GET['Plan_ID'] . "' and sjzt = '上架' ", " ID desc ", " ID as Plan_ID,famc as Plan_Name,fajcj as Plan_Basic_Price,youhuijia as Plan_Discounted_Price,hxmc as Plan_Unit,fadj as Plan_Deposit,suoluetu " );
	$Arr_Plan_List[0]['Thum_Pic_Url'] = $Arr_Plan_List[0]['fil_suoluetu'];
	unset( $Arr_Plan_List[0]['fil_suoluetu'],$Arr_Plan_List[0]['suoluetu'],$Arr_Plan_List[0]['f_suoluetu'] );
	$CALLBACK_DATA['Plan_List'] = $Arr_Plan_List[0];
	# @ Value_Added_Pack List
	$Arr_Value_Added_Pack_ID = listCoreBySql( " select DISTINCT FK from t_zengzhibao_2 where xgfa = '" . $_GET['Plan_ID'] . "' limit 0,100 " );
	if( is_array( $Arr_Value_Added_Pack_ID ) )
	{	# @ Make Filter SQL Condition
		$Arr_Value_Added_Pack_ID = array_column( $Arr_Value_Added_Pack_ID, 'FK' );
		$Str_Value_Added_Pack_SQL_Condition = " ID in ( '" . implode( "','", $Arr_Value_Added_Pack_ID) . "' ) and sjzt = 3 and fenlei != '' and flyw != '' ";
		$CALLBACK_DATA['Value_Added_Pack'] = listCoreBySql( " select DISTINCT fenlei as TypeFilterCn,flyw as TypeFilterEn,'' as DetailList from t_zengzhibao where " . $Str_Value_Added_Pack_SQL_Condition . " order by flsx asc limit 0,100 " );
		$Arr_Value_Added_Pack = listCoreByWhere( "t_zengzhibao", $Str_Value_Added_Pack_SQL_Condition, " ID ASC ", " ID as Pack_ID,zzbmc as Pack_Name,shichangjia as Pack_Basic_Price,youhuijia as Pack_Discounted_Price,fenlei as TypeFilterCn,flyw as TypeFilterEn,huxing as Unit,lbzxt,ifnull( dingjin, 0 ) as Pack_Deposit" );
		if( is_array( $Arr_Value_Added_Pack ) )
		{	# @ Deal Data 
			foreach( $Arr_Value_Added_Pack as $Value_Added_Pack_Key => $Value_Added_Pack_Val )
			{	# @ Deal Data 
				$Arr_Type = array_column( $CALLBACK_DATA['Value_Added_Pack'], 'TypeFilterCn' );
				$Int_Search_Key = array_search( $Value_Added_Pack_Val['TypeFilterCn'], $Arr_Type );
				$Arr_Value_Added_Pack[$Value_Added_Pack_Key]['Thum_Pic_Url'] = $Arr_Value_Added_Pack[$Value_Added_Pack_Key]['fil_lbzxt'];
				unset( $Arr_Value_Added_Pack[$Value_Added_Pack_Key]['TypeFilterCn'],$Arr_Value_Added_Pack[$Value_Added_Pack_Key]['TypeFilterEn'],$Arr_Value_Added_Pack[$Value_Added_Pack_Key]['lbzxt'],$Arr_Value_Added_Pack[$Value_Added_Pack_Key]['fil_lbzxt'],$Arr_Value_Added_Pack[$Value_Added_Pack_Key]['f_lbzxt'] );
				$CALLBACK_DATA['Value_Added_Pack'][$Int_Search_Key]['DetailList'][] = $Arr_Value_Added_Pack[$Value_Added_Pack_Key];
			}	
		}	
		$CALLBACK_DATA['Code'] = 1;
	}
}
/*
|--------------------------------------------------------------------------
| Generate Order
|--------------------------------------------------------------------------
| Norm: 2017/05/09
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_POST['Action_Type'] == 'Generate_Order' && $_POST['Order_Data'] != '' )
{	# @ Order Data
	$Arr_Order_Data = json_decode( $_POST['Order_Data'], true );
	# @ Get New Order ID
	$Str_New_Order_ID = getDynamicID( 'ID', 't_dingdanxitong', "'year'-'ID'", 4 );
	# @ Set Finall Price Default
	$Float_Finall_Price = 0;
	# @ Plan Data
	$Arr_Plan_Data = listCoreByWhere( "t_yangbanfang", " ID = '" . $Arr_Order_Data['Plan_ID'] . "' and sjzt = '上架' ", " ID desc ", " ID as Plan_ID,famc as Plan_Name,ifnull( fajcj , 0 ) as Plan_Basic_Price,ifnull( youhuijia , 0 ) as Plan_Discounted_Price,ifnull( fadj , 0 ) as Plan_Deposit,suoluetu " );
	# @ Value_Added_Pack Data
	if( is_array( $Arr_Order_Data['Value_Added_Pack_Data'] ) )
	{
		foreach( $Arr_Order_Data['Value_Added_Pack_Data'] as $Value_Added_Pack_Val )
		{	# @ Get Value_Added_Pack Data
			$Arr_Value_Added_Pack = listCoreByWhere( "t_zengzhibao", " ID = '" . $Value_Added_Pack_Val['Value_Added_Pack_ID'] . "' ", "", " ID as Pack_ID,zzbmc as Pack_Name,shichangjia as Pack_Basic_Price,youhuijia as Pack_Discounted_Price,lbzxt,dingjin as Pack_Deposit" );
			# @ Update Finall Price
			$Float_Finall_Price = $Float_Finall_Price + ( $Arr_Value_Added_Pack[0]['Pack_Deposit'] * $Value_Added_Pack_Val['Value_Added_Pack_Quantity'] );
			# @ Insert Value_Added_Pack Order Data 
			insertCoreBySql( " INSERT INTO t_dingdanxitong_1 ( FK, zzbbh, zzbmc, xgsl, zzbjcj, zzbyhj, zzbsltdz ) VALUES ( '" . $Str_New_Order_ID . "', '" . $Arr_Value_Added_Pack[0]['Pack_ID'] . "', '" . $Arr_Value_Added_Pack[0]['Pack_Name'] . "', " . $Value_Added_Pack_Val['Value_Added_Pack_Quantity'] . ", " . $Arr_Value_Added_Pack[0]['Pack_Basic_Price'] . ", " . $Arr_Value_Added_Pack[0]['Pack_Discounted_Price'] . ", '" . $Arr_Value_Added_Pack[0]['fil_lbzxt'] . "' ) " );
			# @ Unset Data
			unset( $Arr_Value_Added_Pack );
		}
	}
	# @ Update Finall Price
	if( $Arr_Order_Data['OrderType'] != 1 ){ $Float_Finall_Price = $Float_Finall_Price + $Arr_Plan_Data[0]['Plan_Deposit']; }
	# @ Insert Main Order Data 
	insertCoreBySql( " INSERT INTO t_dingdanxitong ( ID, ddlx, ddzt, yzbh, xsbh, zongjine, fabh, famc, fajcj, fayhj, fasltdz, shijianchuo, gshsj ) VALUES ( '" . $Str_New_Order_ID . "', '" . $Arr_Order_Data['OrderType'] . "', 0, '" . $Arr_Order_Data['Client_ID'] . "', '" . $_SESSION['Login_Info']['ID'] . "', " . $Float_Finall_Price . ", '" . $Arr_Plan_Data[0]['Plan_ID'] . "', '" . $Arr_Plan_Data[0]['Plan_Name'] . "', " . $Arr_Plan_Data[0]['Plan_Basic_Price'] . ", " . $Arr_Plan_Data[0]['Plan_Discounted_Price'] . ", '" . $Arr_Plan_Data[0]['fil_suoluetu'] . "', '" . time() . "', '" . date( " Y-m-d H:i:s " ) . "' ) " );
	# @ Return Data
	$CALLBACK_DATA['Order_ID'] = $Str_New_Order_ID;
	$CALLBACK_DATA['Code'] = 1;
}
if( $_GET['Data_Type'] == 'JP' ){ die( "$callback(" . json_encode( $CALLBACK_DATA ) . ")" ); }else{ die( json_encode( $CALLBACK_DATA ) ); }	
?>