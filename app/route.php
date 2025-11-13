<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//use app\api\logic\common\UploadBase64Logic;
use app\api\model\Contestant;
use app\api\model\ContestantImg;
use app\api\model\ContestantPoster;
use think\Db;
use think\Exception;
use think\Request;
use think\Route;

// 公共
Route::group(':version/common', function () {
    //定时器用
    Route::resource('Crontab', 'api/:version.common.Crontab');
    // 获取小程序二维码
    Route::resource('FetchQRCode', 'api/:version.common.FetchQRCode');
    // 获取小程序码
    Route::resource('GetQRCode', 'api/:version.common.GetQRCode');
    //上传文件
    Route::resource('Upload', 'api/:version.common.Upload');
    //上传文件，返回文件大小
    //Route::resource('UploadFile', 'api/:version.common.UploadFile');
    //上传Base64文件
    Route::resource('UploadBase64', 'api/:version.common.UploadBase64');
    // UE富文本编辑器文件上传
    Route::resource('UEUpload', 'api/:version.common.UEUpload');
    // 统一下单
    Route::resource('UnionOrderPayment', 'api/:version.common.UnionOrderPayment');
    // 统一下单-支付回调-用户端
    Route::resource('UnionOrderPaymentNotify', 'api/:version.common.UnionOrderPaymentNotify');
    // 统一下单-订单查询
    Route::resource('UnionOrderQuery', 'api/:version.common.UnionOrderQuery');
    // 统一下单-订单退款
    Route::resource('UnionOrderRefund', 'api/:version.common.UnionOrderRefund');
    // 微信登录
    Route::resource('WechatLogin', 'api/:version.common.WechatLogin');
    // 苹果登录
    Route::resource('IosLogin', 'api/:version.common.IosLogin');
    // 皮卡图片处理
    Route::resource('PicupShop', 'api/:version.common.PicupShop');
    // 腾讯电子签-回调
    Route::resource('EssCallback', 'api/:version.common.EssCallback');
    // 【临时接口】根据手机号清理用户信息
    Route::resource('ClearUser', 'api/:version.common.ClearUser');
    //密码登录
    Route::resource('LoginByPassword', 'api/:version.common.LoginByPassword');
    //手机验证码登录
    Route::resource('LoginByCode', 'api/:version.common.LoginByCode');
    //获取手机号
    Route::resource('GetMobile', 'api/:version.common.GetMobile');
    //根据卡号获取银行信息
    Route::resource('GetBankByNum', 'api/:version.common.GetBankByNum');
    //获取手机号
    Route::resource('log', 'api/:version.common.Log');
    //cas单点登录
    Route::resource('AdminLogin', 'api/:version.common.AdminLogin');
    //cas单点登出
    Route::resource('LoginOut', 'api/:version.common.LoginOut');
    //验证码
    Route::resource('Captcha', 'api/:version.common.Captcha');
    //发送短信验证码
    Route::resource('SendSmsCode', 'api/:version.common.SendSmsCode');
    //物流订阅回调
    Route::resource('KuaiDiCallBack', 'api/:version.common.KuaiDiCallBack');
    Route::resource('ESign', "api/:version.common.ESign");
});

// 用户端
Route::group(':version/mini', function () {
    //申请开票
    Route::resource('Invoice', 'api/:version.mini.Invoice');
    //商品参数/功效/适用人群/产品类型
    Route::resource('Parameter','api/:version.mini.Parameter');
    //专家资料
    Route::resource('Expert','api/:version.mini.Expert');
    //发票抬头
    Route::resource('InvoiceTitle', 'api/:version.mini.InvoiceTitle');
    //体检报告
    Route::resource('MedicalReport', 'api/:version.mini.MedicalReport');
    //购物车
    Route::resource('Cart', 'api/:version.mini.Cart');
    //银行卡管理
    Route::resource('BankCard', 'api/:version.mini.BankCard');
    //收货地址
    Route::resource('Address', 'api/:version.mini.Address');
    //协议中心
    Route::resource('Agreement', 'api/:version.mini.Agreement');
    //帮助手册
    Route::resource('Help', 'api/:version.mini.Help');
    //帮助手册-分类
    Route::resource('HelpCategory', 'api/:version.mini.HelpCategory');
    //商品分类
    Route::resource('Category', 'api/:version.mini.Category');
    //商品
    Route::resource('Product', 'api/:version.mini.Product');
    //获取商品规格
    //Route::resource('ProductAttribute', 'api/:version.mini.ProductAttribute');
    // 用户信息
    Route::resource('User', 'api/:version.mini.User');
    // 首页海报
    Route::resource('Poster', 'api/:version.mini.Poster');
    // 分类
    Route::resource('Cate', 'api/:version.mini.Cate');
    // 书院
    Route::resource('College', 'api/:version.mini.College');
    // 投诉建议
    Route::resource('Complaint', 'api/:version.mini.Complaint');
    // 公告
    Route::resource('Notification', 'api/:version.mini.Notification');
    //签到
    Route::resource('Sign', 'api/:version.mini.Sign');
    //配置
    Route::resource('Config', 'api/:version.mini.Config');
    //订单
    Route::resource('Order', 'api/:version.mini.Order');
    //查看物流
    Route::resource('OrderPath', 'api/:version.mini.OrderPath');
    //取消订单
    Route::resource('OrderCancel', 'api/:version.mini.OrderCancel');
    //订单确认收货
    Route::resource('OrderConfirm', 'api/:version.mini.OrderConfirm');
    //订单提交报告
    Route::resource('OrderSetMedicalReport', 'api/:version.mini.OrderSetMedicalReport');
    //支付
    Route::resource('Pay', 'api/:version.mini.Pay');
    //支付回调
    Route::resource('WxPayCallback', 'api/:version.mini.WxPayCallback');
    //收藏
    Route::resource('Collect', 'api/:version.mini.Collect');
    //评价
    Route::resource('Evaluate', 'api/:version.mini.Evaluate');
    //签到
    Route::resource('Sign', 'api/:version.mini.Sign');
    //心得
    Route::resource('Feel', 'api/:version.mini.Feel');
    //消息
    Route::resource('Message', 'api/:version.mini.Message');
    //轮播
    Route::resource('Banner', 'api/:version.mini.Banner');
    //浏览记录
    Route::resource('Footprint', 'api/:version.mini.Footprint');
    //标签
    Route::resource('Tag', 'api/:version.mini.Tag');
    //取消订单原因
    Route::resource('Reason', 'api/:version.mini.Reason');
    //售后申请
    Route::resource('AfterSale', 'api/:version.mini.AfterSale');
    //售后跟踪
    Route::resource('AfterSaleLog', 'api/:version.mini.AfterSaleLog');
    //推广员
    Route::resource('Retail', 'api/:version.mini.Retail');
    //消息
    Route::resource('Msg', 'api/:version.mini.Msg');
    //提现
    Route::resource('CashOut', 'api/:version.mini.CashOut');
    //推广订单
    Route::resource('CommissionOrder', 'api/:version.mini.CommissionOrder');
    //账单
    Route::resource('Bill', 'api/:version.mini.Bill');
    //e签宝签署
    Route::resource('ESign', 'api/:version.mini.ESign');
    //合伙人
    Route::resource('Partner', 'api/:version.mini.Partner');
    //2+1分销订单
    Route::resource('PartnerOrder', 'api/:version.mini.PartnerOrder');
});

// 管理端
Route::group(':version/cms', function () {
    //2+1线下分润明细
    Route::resource('PartnerOrderOutline','api/:version.cms.PartnerOrderOutline');
    //合伙人推广统计
    Route::resource('PartnerOrderStat','api/:version.cms.PartnerOrderStat');
    //合伙人团队成员
    Route::resource('PartnerMember', 'api/:version.cms.PartnerMember');
    //合伙人变更类型
    Route::resource('PartnerSetType', 'api/:version.cms.PartnerSetType');
    //合伙人设置状态
    Route::resource('PartnerSetStatus', 'api/:version.cms.PartnerSetStatus');
    //合伙人推广订单
    Route::resource('PartnerOrder', 'api/:version.cms.PartnerOrder');
    //合伙人审核设置备注
    Route::resource('PartnerReviewSetNote', 'api/:version.cms.PartnerReviewSetNote');
    //合伙人审核
    Route::resource('PartnerReview', 'api/:version.cms.PartnerReview');
    //合伙人管理
    Route::resource('Partner', 'api/:version.cms.Partner');
    //银行卡
    Route::resource('BankCard', 'api/:version.cms.BankCard');
    //发票管理
    Route::resource('Invoice', 'api/:version.cms.Invoice');
    //收货地址管理
    Route::resource('Address', 'api/:version.cms.Address');
    //收货地址设置默认
    Route::resource('AddressSetDefault', 'api/:version.cms.AddressSetDefault');
    //订单取消原因上移/下移
    Route::resource('ReasonSetOrderNumber', 'api/:version.cms.ReasonSetOrderNumber');
    //订单取消原因
    Route::resource('Reason', 'api/:version.cms.Reason');
    //体检报告管理审核
    Route::resource('MedicalReportSetStatus', 'api/:version.cms.MedicalReportSetStatus');
    //体检报告管理
    Route::resource('MedicalReport', 'api/:version.cms.MedicalReport');
    //分销员管理设置审核状态
    Route::resource('RetailSetReviewStatus', 'api/:version.cms.RetailSetReviewStatus');
    //分销员管理设置备注
    Route::resource('RetailSetNote', 'api/:version.cms.RetailSetNote');
    //分销员管理设置类型
    Route::resource('RetailSetType', 'api/:version.cms.RetailSetType');
    //分销员管理设置状态
    Route::resource('RetailSetStatus', 'api/:version.cms.RetailSetStatus');
    //分销员管理
    Route::resource('Retail', 'api/:version.cms.Retail');
    //分销员团队成员
    Route::resource('RetailMember', 'api/:version.cms.RetailMember');
    //配置
    Route::resource('Config', 'api/:version.cms.Config');
    //配置新增
    Route::resource('ConfigSave', 'api/:version.cms.ConfigSave');
    //专家资料
    Route::resource('Expert', 'api/:version.cms.Expert');
    //专家资料设置状态
    Route::resource('ExpertSetStatus', 'api/:version.cms.ExpertSetStatus');
    //专家资料-上移/下移
    Route::resource('ExpertSetOrderNumber', 'api/:version.cms.ExpertSetOrderNumber');
    //字段-国家
    Route::resource('DictionaryCountry', 'api/:version.cms.DictionaryCountry');
    //协议中心
    Route::resource('Agreement', 'api/:version.cms.Agreement');
    //字典
    Route::resource('Dictionary', 'api/:version.cms.Dictionary');
    //字典类型
    Route::resource('DictionaryType', 'api/:version.cms.DictionaryType');
    //字典设置状态
    Route::resource('DictionarySetStatus', 'api/:version.cms.DictionarySetStatus');
    //字典类型设置状态
    Route::resource('DictionaryTypeSetStatus', 'api/:version.cms.DictionaryTypeSetStatus');
    //帮助手册-分类
    Route::resource('HelpCategory', 'api/:version.cms.HelpCategory');
    //帮助手册
    Route::resource('Help', 'api/:version.cms.Help');
    //帮助手册设置状态
    Route::resource('HelpSetStatus', 'api/:version.cms.HelpSetStatus');
    //首页弹窗海报管理
    Route::resource('Poster', 'api/:version.cms.Poster');
    //首页弹窗海报管理设置状态
    Route::resource('PosterSetStatus', 'api/:version.cms.PosterSetStatus');
    //系统划分
    Route::resource('Site', 'api/:version.cms.Site');
    // 商品分类
    Route::resource('Category', 'api/:version.cms.Category');
    // 商品分类
    Route::resource('CategoryVis', 'api/:version.cms.CategoryVis');
    //商品分类上移/下移
    Route::resource('CategorySetOrderNumber', 'api/:version.cms.CategorySetOrderNumber');
    // 商品参数
    Route::resource('Parameter', 'api/:version.cms.Parameter');
    //商品参数-设置状态
    Route::resource('ParameterSetStatus', 'api/:version.cms.ParameterSetStatus');
    //商品库
    Route::resource('Product', 'api/:version.cms.Product');
    //商品库-设置状态
    Route::resource('ProductSetVis', 'api/:version.cms.ProductSetVis');
    //商品库-设置分类
    Route::resource('ProductSetCategory', 'api/:version.cms.ProductSetCategory');
    //商品库-设置推荐
    Route::resource('ProductSetRecommend', 'api/:version.cms.ProductSetRecommend');
    //推荐商品上移/下移
    Route::resource('ProductSetOrderNumber', 'api/:version.cms.ProductSetOrderNumber');
    //商品-规格
    Route::resource('ProductAttribute', 'api/:version.cms.ProductAttribute');
    //规格库
    Route::resource('Attribute', 'api/:version.cms.Attribute');
    //规格库-设置状态
    Route::resource('AttributeSetStatus', 'api/:version.cms.AttributeSetStatus');
    // 用户管理
    Route::resource('User', 'api/:version.cms.User');
    //用户管理-批量修改状态
    Route::resource('UserSetDisabled', 'api/:version.cms.UserSetDisabled');
    // 学生导出
    Route::resource('UserExport', 'api/:version.cms.UserExport');
    // 轮播
    Route::resource('Banner', 'api/:version.cms.Banner');
    // 轮播显示/隐藏
    Route::resource('BannerSetStatus', 'api/:version.cms.BannerSetStatus');
    // 管理员
    Route::resource('Admin', 'api/:version.cms.Admin');
    // 重置密码
    Route::resource('AdminResetPassword', 'api/:version.cms.AdminResetPassword');
    // 修改密码
    Route::resource('AdminEditPassword', 'api/:version.cms.AdminEditPassword');
    // 设置状态
    Route::resource('AdminSetStatus', 'api/:version.cms.AdminSetStatus');
    // 管理员权限设置
    Route::resource('AdminSetPermission', 'api/:version.cms.AdminSetPermission');
    // 管理员当前用户信息
    Route::resource('AdminInfo', 'api/:version.cms.AdminInfo');
    // 菜单
    Route::resource('AdminMenu', 'api/:version.cms.AdminMenu');
    // 菜单设置是否启用
    Route::resource('AdminMenuVis', 'api/:version.cms.AdminMenuVis');
    // 角色
    Route::resource('AdminRole', 'api/:version.cms.AdminRole');
    // 角色设置状态
    Route::resource('AdminRoleStatus', 'api/:version.cms.AdminRoleStatus');
    // 角色添加人员
    Route::resource('AdminRoleSetAdmin', 'api/:version.cms.AdminRoleSetAdmin');
    // 角色设置权限
    Route::resource('AdminRoleSetMenus', 'api/:version.cms.AdminRoleSetMenus');
    // 日志
    Route::resource('AdminLog', 'api/:version.cms.AdminLog');
    // 日志导出
    Route::resource('AdminLogExport', 'api/:version.cms.AdminLogExport');
    // 管理员/教师导出
    Route::resource('AdminExport', 'api/:version.cms.AdminExport');
    // 订单
    Route::resource('Order', 'api/:version.cms.Order');
    //订单发货
    Route::resource('OrderShipment', 'api/:version.cms.OrderShipment');
    //设置订单备注
    Route::resource('OrderSetNote', 'api/:version.cms.OrderSetNote');
    //订单修改价格
    Route::resource('OrderSetPrice', 'api/:version.cms.OrderSetPrice');
    //修改订单地址
    Route::resource('OrderAddress', 'api/:version.cms.OrderAddress');
    //查看物流
    Route::resource('OrderPath', 'api/:version.cms.OrderPath');
    //取消订单
    Route::resource('OrderCancel', 'api/:version.cms.OrderCancel');
    //订单统计
    Route::resource('OrderCount', 'api/:version.cms.OrderCount');
    //快递公司
    Route::resource('Delivery', 'api/:version.cms.Delivery');
    // 签到信息
    Route::resource('Sign', 'api/:version.cms.Sign');
    // 签到信息导出
    Route::resource('SignExport', 'api/:version.cms.SignExport');
    // 评价
    Route::resource('Evaluate', 'api/:version.cms.Evaluate');
    // 评价导出
    Route::resource('EvaluateExport', 'api/:version.cms.EvaluateExport');
    // 评价导出
    Route::resource('Analysis', 'api/:version.cms.Analysis');
    // 年级
    Route::resource('Grade', 'api/:version.cms.Grade');
    // 班级
    Route::resource('Class', 'api/:version.cms.UserClass');
    // 需求建议
    Route::resource('Complaint', 'api/:version.cms.Complaint');
    //售后
    Route::resource('AfterSale', 'api/:version.cms.AfterSale');
    //售后审核
    Route::resource('AfterSaleSetStaus','api/:version.cms.AfterSaleSetStatus');
    //售后状态统计
    Route::resource('AfterSaleCount','api/:version.cms.AfterSaleCount');
    //售后备注
    Route::resource('AfterSaleSetNote','api/:version.cms.AfterSaleSetNote');
    //售后统计
    Route::resource('AfterSaleStat','api/:version.cms.AfterSaleStat');
    //工作台
    Route::resource('Desk','api/:version.cms.Desk');
    //推广订单
    Route::resource('CommissionOrder','api/:version.cms.CommissionOrder');
    //推广统计
    Route::resource('CommissionOrderStat','api/:version.cms.CommissionOrderStat');
    //分润统计
    Route::resource('CommissionOrderOutline','api/:version.cms.CommissionOrderOutline');
    //出品方
    Route::resource('Producer','api/:version.cms.Producer');
    //出品方设置状态
    Route::resource('ProducerSetStatus','api/:version.cms.ProducerSetStatus');
    //特邀经销商
    Route::resource('Dealer','api/:version.cms.Dealer');
    //特邀经销商设置状态
    Route::resource('DealerSetStatus','api/:version.cms.DealerSetStatus');
    //大区推广员
    Route::resource('Region','api/:version.cms.Region');
    //大区推广员设置状态
    Route::resource('RegionSetStatus','api/:version.cms.RegionSetStatus');
    //渠道商
    Route::resource('Channel','api/:version.cms.Channel');
    //渠道商设置状态
    Route::resource('ChannelSetStatus','api/:version.cms.ChannelSetStatus');
    //提现
    Route::resource('CashOut','api/:version.cms.CashOut');
    //提现设置备注
    Route::resource('CashOutSetNote','api/:version.cms.CashOutSetNote');
    //提现审核
    Route::resource('CashOutSetStatus','api/:version.cms.CashOutSetStatus');
    //账单
    Route::resource('Bill','api/:version.cms.Bill');
});

Route::miss('Error/index');
$request = Request::instance();
if ($request->method() === "OPTIONS") {
    exit(json_encode(array('error' => 200, 'message' => 'option true.')));
} elseif ($request->method() === "HEAD") {
    exit(json_encode(array('error' => 200, 'message' => 'option true.')));
}
return [
    '__pattern__' => [
        'name' => '\w+',
    ],


];
