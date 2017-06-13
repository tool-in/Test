<?php
require_once("Public/Header.php");
$CALLBACK_DATA = array( "Code" => 0 );
/*
|--------------------------------------------------------------------------
| Plan List
|--------------------------------------------------------------------------
| Norm: 2017/05/08
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Plan_List' )
{	# @ SQL Condition
	$Str_SQL_Condition = " loupan = '" . $_SESSION['Login_Info']['BuildNum'] . "' and sjzt = '上架' ";
	# @ Count Pages
	$Arr_Data_Quantity = listCoreBySql( " select count( ID ) as DataQuantity from t_yangbanfang where " . $Str_SQL_Condition );
	if( is_array( $Arr_Data_Quantity ) ){ $CALLBACK_DATA['MaxPage'] = ceil( $Arr_Data_Quantity[0]['DataQuantity'] / 15 ); }else{ $CALLBACK_DATA['DataQuantity'] = 1; }
	# @ SQL Limit
	if( $_GET['Page'] && $_GET['Page'] > 1 ){ $Str_SQL_Limit = ' limit '.( ( $_GET['Page'] - 1 ) * 15 ).',15 '; $CALLBACK_DATA['NowPage'] = $_GET['Page']; }else{ $Str_SQL_Limit = ' limit 0,15 '; $CALLBACK_DATA['NowPage'] = 1; }
	# @ Plan List
	$CALLBACK_DATA['Plan_List'] = listCoreByWhere( "t_yangbanfang", $Str_SQL_Condition, " ID desc " . $Str_SQL_Limit, "ID as Plan_ID,famc as Plan_Name,fajcj as Plan_Basic_Price,youhuijia as Plan_Discounted_Price,hxmc as Plan_Unit,fafg as Plan_Style,truncate( mianji, 2 ) as Plan_Area,dingpei as Plan_Standard,suoluetu" );
	if( is_array( $CALLBACK_DATA['Plan_List'] ) )
	{	# @ Delete Useless Field
		foreach( $CALLBACK_DATA['Plan_List'] as $Plan_Key => $Plan_Val )
		{	# @ Make New Pic Field
			$CALLBACK_DATA['Plan_List'][$Plan_Key]['Thum_Pic_Url'] = $Plan_Val['fil_suoluetu'];
			unset( $CALLBACK_DATA['Plan_List'][$Plan_Key]['fil_suoluetu'],$CALLBACK_DATA['Plan_List'][$Plan_Key]['suoluetu'],$CALLBACK_DATA['Plan_List'][$Plan_Key]['f_suoluetu'] );
		}
	}
	$CALLBACK_DATA['Code'] = 1;
}
if( $_GET['Data_Type'] == 'JP' ){ die( "$callback(" . json_encode( $CALLBACK_DATA ) . ")" ); }else{ die( json_encode( $CALLBACK_DATA ) ); }	
?>