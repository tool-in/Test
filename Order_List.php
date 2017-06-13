<?php
require_once("Public/Header.php");
$CALLBACK_DATA = array( "Code" => 0 );
/*
|--------------------------------------------------------------------------
| Order List
|--------------------------------------------------------------------------
| Norm: 2017/05/10
|--------------------------------------------------------------------------
| Note: /
|--------------------------------------------------------------------------
*/
if( $_GET['Action_Type'] == 'Order_List' )
{	# @ Filter Make Client Time
	if( $_GET['Filter_Data_A'] == 'A' ){ $Str_Filter_Order_Time = " and shijianchuo > " . ( time() - 2592000 ); }
	if( $_GET['Filter_Data_A'] == 'B' ){ $Str_Filter_Order_Time = " and shijianchuo > " . ( time() - 7776000 ); }
	if( $_GET['Filter_Data_A'] == 'C' ){ $Str_Filter_Order_Time = " and shijianchuo > " . ( time() - 31104000 ); }
	if( $_GET['Filter_Data_A'] == 'D' ){ $Str_Filter_Order_Time = " and shijianchuo < " . strtotime( " 2017 " ); }
	# @ Filter Order Status
	if( $_GET['Filter_Data_B'] == 'A' ){ $Str_Filter_Order_Status = " and ddzt = 0 "; }
	if( $_GET['Filter_Data_B'] == 'B' ){ $Str_Filter_Order_Status = " and ddzt = 1 "; }
	# @ Filter Order Client
	if( $_GET['Filter_Data_C'] != '' ){ $Str_Filter_Order_Client = " and yzbh = '" . $_GET['Filter_Data_C'] . "'"; }
	# @ Search Filter
	if( $_GET['Search'] ){ $Str_Search_Filter = " and ( ( select xingming from t_loupanyezhu where ID = t_dingdanxitong.yzbh limit 0,1 ) like '%" . $_GET['Search'] . "%' or ID like '%" . $_GET['Search'] . "%' )"; }
	# @ Count Pages
	$Arr_Data_Quantity = listCoreBySql( " select count( ID ) as DataQuantity from t_dingdanxitong where xsbh = '" .  $_SESSION['Login_Info']['ID'] . "'" . $Str_Filter_Order_Time . $Str_Filter_Order_Status . $Str_Filter_Order_Client . $Str_Search_Filter );
	if( is_array( $Arr_Data_Quantity ) ){ $CALLBACK_DATA['MaxPage'] = ceil( $Arr_Data_Quantity[0]['DataQuantity'] / 15 ); }else{ $CALLBACK_DATA['DataQuantity'] = 1; }
	# @ SQL Limit
	if( $_GET['Page'] && $_GET['Page'] > 1 ){ $Str_SQL_Limit = ' limit '.( ( $_GET['Page'] - 1 ) * 15 ).',15 '; $CALLBACK_DATA['NowPage'] = $_GET['Page']; }else{ $Str_SQL_Limit = ' limit 0,15 '; $CALLBACK_DATA['NowPage'] = 1; }
	# @ SQL Plan Info Part
	$Str_Plan_Info_Part = " , fabh as Plan_ID, famc as Plan_Name, ifnull( fajcj, 0 ) as Plan_Basic_Price, ifnull( fayhj, 0 ) as Plan_Discounted_Price, ( select hxmc from t_yangbanfang where ID = t_dingdanxitong.fabh ) as Unit, ( select mianji from t_yangbanfang where ID = t_dingdanxitong.fabh ) as Area, ( select fafg from t_yangbanfang where ID = t_dingdanxitong.fabh ) as Style";
	$Str_SQL_Finall = " select ID as Order_ID,yzbh as Client_ID,gshsj as Order_Time, ( select xingming from t_loupanyezhu where ID = t_dingdanxitong.yzbh limit 0,1 ) as Client_Name, ifnull( ( select sum( xgsl ) from t_dingdanxitong_1 where FK = t_dingdanxitong.ID ), 0 ) as Value_Added_Pack_Quantity, ifnull( zongjine, 0 ) as Total_Amount, ddzt as Order_Status, ddlx as Order_Type ". $Str_Plan_Info_Part ." from t_dingdanxitong where xsbh = '" .  $_SESSION['Login_Info']['ID'] . "'" . $Str_Search_Filter . $Str_Filter_Order_Time . $Str_Filter_Order_Status . $Str_Filter_Order_Client . $Str_SQL_Limit;               
	# @ Order List Data
	$CALLBACK_DATA['Order_List_Data'] = listCoreBySql( $Str_SQL_Finall );
	# @ Client List
	$CALLBACK_DATA['Order_Client_List'] = listCoreBySql( " select distinct yzbh as Client_ID,( select xingming from t_loupanyezhu where ID = t_dingdanxitong.yzbh limit 0,1 ) as Client_Name from t_dingdanxitong where xsbh = '" .  $_SESSION['Login_Info']['ID'] . "'" . $Str_Filter_Order_Time . $Str_Filter_Order_Status . $Str_Filter_Order_Client );
	# @ Return Code
	$CALLBACK_DATA['Code'] = 1;
}
if( $_GET['Data_Type'] == 'JP' ){ die( "$callback(" . json_encode( $CALLBACK_DATA ) . ")" ); }else{ die( json_encode( $CALLBACK_DATA ) ); }	
?>