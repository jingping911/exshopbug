(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["apiMember-orderdetail-main"],{"06f8":function(t,e,i){"use strict";i.r(e);var a=i("5314"),o=i.n(a);for(var d in a)"default"!==d&&function(t){i.d(e,t,(function(){return a[t]}))}(d);e["default"]=o.a},"2e66":function(t,e,i){"use strict";i.r(e);var a=i("fba1"),o=i("06f8");for(var d in o)"default"!==d&&function(t){i.d(e,t,(function(){return o[t]}))}(d);i("2ffd");var r,s=i("f0c5"),n=Object(s["a"])(o["default"],a["b"],a["c"],!1,null,"441785d0",null,!1,a["a"],r);e["default"]=n.exports},"2ffd":function(t,e,i){"use strict";var a=i("aa01"),o=i.n(a);o.a},5314:function(t,e,i){"use strict";(function(t){var a=i("4ea4");Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("96cf");var o=a(i("1da1")),d=i("ffcc"),r=i("b3d2"),s={onShow:function(){(0,r.toLogin)()},data:function(){return{productList:[],orderId:0,a:{path:"微信",cost:500,deliver_cost:"免邮",discount:"36.8"},orderInfo:{},orderGoods:[],handleOption:{},pintuan:"0",avator:"https://imgt1.oss-cn-shanghai.aliyuncs.com/ecAllRes/images/missing-face.png",pintuan_status:"",pt_id:"0"}},components:{},onLoad:function(t){this.orderId=t.id,this.getOrderDetail()},mounted:function(){return(0,o.default)(regeneratorRuntime.mark((function t(){return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:case"end":return t.stop()}}),t)})))()},methods:{getOrderDetail:function(){var e=this;return(0,o.default)(regeneratorRuntime.mark((function i(){var a;return regeneratorRuntime.wrap((function(i){while(1)switch(i.prev=i.next){case 0:return i.next=2,(0,d.orderdetailGetApi)({orderId:e.orderId});case 2:a=i.sent,e.orderInfo=a.data.orderInfo,e.orderGoods=a.data.orderGoods,e.handleOption=a.data.handleOption,e.a=a.data.a,"1"===a.data.orderInfo.order_type&&(e.pintuan="1",e.pintuan_status=a.data.orderInfo.pintuan_status_text,e.pt_id=a.data.orderInfo.pt_id),t.log(e.orderGoods);case 9:case"end":return i.stop()}}),i)})))()},logisticstrackGo:function(t){uni.redirectTo({url:"/apiUtil/logisticstrack/main?id="+t})},payTimer:function(){var t=this,e=this.orderInfo;setInterval((function(){e.add_time-=1,t.orderInfo=e}),1e3)},goodsDetail:function(t){uni.navigateTo({url:"/apiShop/goods/main?id="+t})},goodsJudge:function(t,e){uni.navigateTo({url:"/apiMember/judge/main?orderId="+e+"&goodsId="+t})},getgoodsJudge:function(e){t.log(e),uni.navigateTo({url:"/apiShop/userjudge/main?id="+e})},goodsAfterSale:function(t){uni.navigateTo({url:"/apiMember/aftersale/main?orderId="+t})}}};e.default=s}).call(this,i("5a52")["default"])},aa01:function(t,e,i){var a=i("ec9e");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var o=i("4f06").default;o("23c2e7a0",a,!0,{sourceMap:!1,shadowMode:!1})},ec9e:function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 页面左右间距 */\n/* 文字尺寸 */\n/*文字颜色*/\n/* 边框颜色 */\n/* 图片加载中颜色 */\n/* 行为相关颜色 */uni-page-body[data-v-441785d0]{height:100vh;width:100%}.container[data-v-441785d0]{padding:0;height:auto;width:100%;background:#f4f4f4}.order-info[data-v-441785d0]{margin-top:10px;margin-bottom:10px;padding:10px 0;background:#fff;height:auto;overflow:hidden;width:100%;border-bottom:.01rem solid #f4f4f4;border-top:.01rem solid #f4f4f4}.item-a[data-v-441785d0]{padding-left:15.625px;height:15px;padding-bottom:5px;line-height:15px;font-size:12px;color:#666}.item-b[data-v-441785d0]{padding-left:15.625px;height:15px;line-height:15px;font-size:12px;color:#666}.item-c[data-v-441785d0]{height:51.5px;width:100%;margin-left:%?31.25?%;border-top:.5px solid #f4f4f4;line-height:51.5px;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between}.item-c .l[data-v-441785d0]{float:left}.item-c .r[data-v-441785d0]{height:51.5px;float:right;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;padding-right:8px}.item-c .r .btn[data-v-441785d0]{float:right}.item-c .cost[data-v-441785d0]{color:#ff6700}.item-c .btn[data-v-441785d0]{line-height:33px;border-radius:2.5px;text-align:center;margin:0 7.5px;padding:0 10px;height:33px}.item-c .btn.active[data-v-441785d0]{background:#ff6700;color:#fff}.order-goods[data-v-441785d0]{margin-top:10px;background:#fff;width:100%}.order-goods .h[data-v-441785d0]{height:40px;line-height:40px;margin-left:15.625px;border-bottom:.5px solid #f4f4f4;padding-right:15.625px}.order-goods .h .label[data-v-441785d0]{float:left;font-size:%?24?%;color:#333}.order-goods .h .status[data-v-441785d0]{float:right;font-size:%?24?%;color:#b4282d}.order-goods .item[data-v-441785d0]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;height:96px;margin-left:15.625px;margin-right:15.625px;border-bottom:.5px solid #f4f4f4}.order-goods .item[data-v-441785d0]:last-child{border-bottom:none}.order-goods .item .img[data-v-441785d0]{height:72.915px;width:72.915px;background:#f4f4f4}.order-goods .item .img uni-image[data-v-441785d0]{height:72.915px;width:72.915px}.order-goods .item .info[data-v-441785d0]{-webkit-box-flex:1;-webkit-flex:1;flex:1;height:72.915px;margin-left:20px}.order-goods .item .t[data-v-441785d0]{margin-top:4px;height:16.5px;line-height:16.5px;margin-bottom:5.25px}.order-goods .item .t .name[data-v-441785d0]{width:%?420?%;padding-right:%?15?%;display:block;float:left;height:16.5px;line-height:16.5px;color:#333;font-size:13px;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.order-goods .item .t .number[data-v-441785d0]{display:block;float:right;height:16.5px;text-align:right;line-height:16.5px;color:#333;font-size:13px}.order-goods .item .attr[data-v-441785d0]{height:14.5px;width:%?420?%;line-height:14.5px;color:#666;margin-bottom:12.5px;font-size:12px;white-space:nowrap;text-overflow:ellipsis;overflow:hidden}.order-goods .item .price[data-v-441785d0]{height:15px;line-height:15px;color:#333;font-size:13px}.order-goods .btn-group[data-v-441785d0]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;padding:%?15?% %?30?%}.order-goods .btn-group .service[data-v-441785d0]{padding:%?10?% %?15?%;border:%?1?% solid #ccc;border-radius:%?12?%;color:#666;font-size:%?24?%}.order-goods .btn-group .review[data-v-441785d0]{padding:%?10?% %?15?%;border:%?1?% solid #ccc;border-radius:%?12?%;color:#666;font-size:%?24?%}.order-bottom[data-v-441785d0]{height:auto;overflow:hidden;background:#fff;width:100%;border-top:.01rem solid #f4f4f4;margin-bottom:%?10?%}.order-bottom .address[data-v-441785d0]{background:url(https://imgt1.oss-cn-shanghai.aliyuncs.com/ecAllRes/images/address-bg-bd.png) 0 0 repeat-x #fff;padding:20px 15.625px 12.5px}.order-bottom .address .t[data-v-441785d0]{line-height:17.5px;margin-bottom:3.75px;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:start;-webkit-justify-content:start;justify-content:start}.order-bottom .address .name[data-v-441785d0]{width:70px;font-size:12.5px;text-overflow:ellipsis;white-space:nowrap;overflow:hidden}.order-bottom .address .mobile[data-v-441785d0]{font-size:12.5px}.order-bottom .address .b[data-v-441785d0]{line-height:17.5px;font-size:12.5px;word-break:break-all}.order-bottom .total[data-v-441785d0]{height:auto;padding-top:10px;padding-bottom:10px;margin-left:%?31.25?%;margin-right:%?31.25?%}.order-bottom .total .t[data-v-441785d0]{height:15px;line-height:15px;margin-bottom:5px;display:-webkit-box;display:-webkit-flex;display:flex}.order-bottom .total .label[data-v-441785d0]{width:70px;display:inline-block;height:17.5px;line-height:17.5px;font-size:%?24?%;color:#666}.order-bottom .total .txt[data-v-441785d0]{-webkit-box-flex:1;-webkit-flex:1;flex:1;display:inline-block;height:17.5px;line-height:17.5px;font-size:%?24?%;color:#666}.btn-group.bottom[data-v-441785d0]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-pack:end;-webkit-justify-content:flex-end;justify-content:flex-end;padding:%?15?% 0;border-top:%?1?% solid #f4f4f4;margin:%?15?% %?30?%}.btn-group.bottom .del[data-v-441785d0]{padding:%?10?% %?15?%;border:%?1?% solid #ccc;border-radius:%?12?%;color:#666;font-size:%?24?%}.order-bottom .pay-fee[data-v-441785d0]{height:40.5px;line-height:40.5px}.order-bottom .pay-fee .label[data-v-441785d0]{display:inline-block;width:70px;color:#ff6700}.order-bottom .pay-fee .txt[data-v-441785d0]{display:inline-block;width:70px;color:#ff6700}.common-problem[data-v-441785d0]{margin-bottom:%?20?%}.common-problem .h[data-v-441785d0]{padding:%?35?% 0;background:#fff;text-align:center;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.common-problem .h .line[data-v-441785d0]{display:inline-block;height:%?1?%;width:%?100?%;background:#ccc}.common-problem .h .title[data-v-441785d0]{padding:0 %?25?%;background:#fff}.common-problem .b[data-v-441785d0]{padding:%?0?% %?30?%;background:#fff}.common-problem .b .item[data-v-441785d0]{padding-bottom:%?25?%}.common-problem .b .item .question-box[data-v-441785d0]{display:-webkit-box;display:-webkit-flex;display:flex}.common-problem .b .item .question-box .spot[data-v-441785d0]{height:%?8?%;width:%?8?%;background:#b4282d;border-radius:50%;margin-top:%?11?%}.common-problem .b .item .question-box .question[data-v-441785d0]{line-height:%?30?%;padding-left:%?8?%;display:block;font-size:%?26?%;padding-bottom:%?15?%;color:#303030}.common-problem .b .item .answer[data-v-441785d0]{line-height:%?40?%;padding-left:%?16?%;font-size:%?26?%;color:#787878}.common-problem .sublist[data-v-441785d0]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap;-webkit-box-pack:justify;-webkit-justify-content:space-between;justify-content:space-between;width:%?730?%;margin:0 auto}.common-problem .sublist div[data-v-441785d0]{width:%?360?%;background:#fff;margin-bottom:%?10?%;padding-bottom:%?10?%}.common-problem .sublist div img[data-v-441785d0]{display:block;width:%?302?%;height:%?302?%;margin:0 auto}.common-problem .sublist div p[data-v-441785d0]{margin-bottom:%?5?%;text-indent:1em}.common-problem .sublist div p[data-v-441785d0]:nth-child(2){overflow:hidden;text-overflow:ellipsis;white-space:nowrap;width:98%}.common-problem .sublist div p[data-v-441785d0]:nth-child(3){color:#9c3232}.common-problem .sublist .last[data-v-441785d0]{display:block;width:%?302?%;height:%?302?%;margin:0 auto;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-box-orient:vertical;-webkit-box-direction:normal;-webkit-flex-direction:column;flex-direction:column;-webkit-box-align:center;-webkit-align-items:center;align-items:center;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-flex-wrap:wrap;flex-wrap:wrap}.common-problem .sublist .last p[data-v-441785d0]{height:%?33?%;width:100%;line-height:%?33?%;color:#333;font-size:%?33?%;text-align:center}.common-problem .sublist .last .icon[data-v-441785d0]{display:inline-block;width:%?70?%;height:%?70?%;background:url(https://imgt1.oss-cn-shanghai.aliyuncs.com/ecAllRes/images/rightbig.png) no-repeat;background-size:100% 100%;margin-top:%?60?%}.common-problem .sublist div[data-v-441785d0]:nth-child(2n){margin-left:%?10?%}.judge[data-v-441785d0]{padding:%?10?% %?15?%;margin-top:-20px;margin-left:5px;border:%?1?% solid #ccc;border-radius:%?12?%;color:#666;font-size:%?24?%;float:right}.review[data-v-441785d0]{padding:%?10?% %?15?%;margin-top:-20px;border:%?1?% solid #ccc;border-radius:%?12?%;color:#666;font-size:%?24?%;float:right;margin-right:5px}.pt_status[data-v-441785d0]{padding-left:15.625px;height:15px;padding-bottom:5px;line-height:15px;font-size:12px;color:#666}.portrait[data-v-441785d0]{width:50px;height:20px;float:right;margin-right:10px}.portrait_acator[data-v-441785d0]{width:20px;height:20px;margin-right:5px}.portrait_acator_pd[data-v-441785d0]{width:20px;height:20px}',""]),t.exports=e},fba1:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return o})),i.d(e,"c",(function(){return d})),i.d(e,"a",(function(){return a}));var o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",[i("cu-custom",{attrs:{bgColor:"bg-white",isBack:!0}},[i("template",{attrs:{slot:"backText"},slot:"backText"},[t._v("返回")]),i("template",{attrs:{slot:"content"},slot:"content"},[t._v("订单详情")])],2),i("div",{staticClass:"container"},[i("div",{staticClass:"order-bottom"},[i("div",{staticClass:"address"},[i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"name",staticStyle:{overflow:"hidden","text-overflow":"ellipsis",height:"28rpx","white-space":"nowrap"}},[t._v(t._s(t.orderInfo.consignee))]),i("v-uni-text",{staticClass:"mobile"},[t._v(t._s(t.orderInfo.mobile))])],1),i("div",{staticClass:"b"},[t._v(t._s(t.orderInfo.full_region+t.orderInfo.address))])])]),i("div",{staticClass:"order-goods"},[i("div",{staticClass:"h"},[i("div",{staticClass:"label"},[t._v("订单号"),i("span",{},[t._v(t._s(t.orderInfo.order_sn))])]),i("div",{staticClass:"status"},[t._v(t._s(t.orderInfo.order_status_text))])]),i("div",{staticClass:"goods"},[t._l(t.orderGoods,(function(e,a){return i("div",{key:e.id,staticClass:"item",attrs:{"data-index":a}},[i("div",{staticClass:"img"},[i("v-uni-image",{attrs:{src:e.list_pic_url},on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.goodsDetail(e.id)}}})],1),i("div",{staticClass:"info"},[i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"name",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.goodsDetail(e.id)}}},[t._v(t._s(e.goods_name))]),i("v-uni-text",{staticClass:"number"},[t._v("*"+t._s(e.number))])],1),i("div",{staticClass:"attr"},[t._v(t._s(e.goods_specifition_name_value))]),i("div",{staticClass:"price"},[t._v("￥"+t._s(e.retail_price))]),"5"==t.orderInfo.order_status&&"2"==t.orderInfo.shipping_status&&"0"==e.judge?i("div",{staticClass:"judge",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.goodsJudge(e.id,t.orderInfo.id)}}},[t._v("评价")]):t._e(),"1"==e.judge?i("div",{staticClass:"judge"},[t._v("已评价")]):t._e(),i("div",{staticClass:"judge",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.getgoodsJudge(e.id)}}},[t._v("查看评价")])])])})),i("div",{staticClass:"btn-group"},["wait"==t.orderInfo.return_status&&"true"==t.orderInfo.apply_for_status&&"0"!=t.orderInfo.shipping_status?i("div",{staticClass:"service"},[t._v("售后审核中")]):t._e(),"error"==t.orderInfo.return_status&&"true"==t.orderInfo.apply_for_status?i("div",{staticClass:"service"},[t._v("售后审核拒绝")]):t._e(),"succ"==t.orderInfo.return_status&&"true"==t.orderInfo.apply_for_status?i("div",{staticClass:"service"},[t._v("审核已通过")]):t._e(),"0"!=t.orderInfo.order_status&&"1"!=t.orderInfo.order_status&&"2"!=t.orderInfo.order_status&&"4"!=t.orderInfo.order_status&&"false"==t.orderInfo.apply_for_status&&"已收货"!=t.orderInfo.order_status_text?i("div",{staticClass:"service",on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.goodsAfterSale(t.orderInfo.order_sn)}}},[t._v("售后服务")]):t._e()])],2)]),i("div",{staticClass:"order-info"},[i("div",{staticClass:"item-a"},[t._v("下单时间："+t._s(t.orderInfo.add_time))]),i("div",{staticClass:"item-b"},[t._v("订单编号："+t._s(t.orderInfo.order_sn))])]),"1"==t.pintuan?i("div",{staticClass:"order-info"},[i("div",{staticClass:"pt_status"},[t._v(t._s(t.pintuan_status)),i("div",{staticClass:"portrait"},[i("v-uni-image",{staticClass:"portrait_acator",attrs:{src:t.avator}}),"0"!=t.pt_id?i("v-uni-image",{staticClass:"portrait_acator_pd",attrs:{src:t.avator}}):t._e()],1)])]):t._e(),i("div",{staticClass:"order-bottom"},[i("div",{staticClass:"total"},["exchange_goods"!=t.orderInfo.extension_code?i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("支付方式：")]),i("v-uni-text",{staticClass:"txt"},[t._v(t._s(t.a.path))])],1):t._e(),"exchange_goods"==t.orderInfo.extension_code?i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("支付方式：")]),i("v-uni-text",{staticClass:"txt"},[t._v("积分兑换")])],1):t._e(),i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("商品金额：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥+"+t._s(t.a.goods_amount))])],1),i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("运费：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥+"+t._s(t.a.deliver_cost))])],1),"0.00"!=t.a.bonus?i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("红包：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥-"+t._s(t.a.bonus))])],1):t._e(),"0.00"!=t.a.pack_fee?i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("包装费：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥+"+t._s(t.a.pack_fee))])],1):t._e(),"0.00"!=t.a.integral_money?i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("积分抵扣：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥-"+t._s(t.a.integral_money))])],1):t._e(),""!=t.a.inv_type?i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("税额：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥+"+t._s(t.a.tax)+" ("+t._s(t.a.inv_type)+")")])],1):t._e(),i("div",{staticClass:"t"},[i("v-uni-text",{staticClass:"label"},[t._v("订单合计：")]),i("v-uni-text",{staticClass:"txt"},[t._v("￥"+t._s(t.a.cost))])],1)])]),i("div",{staticStyle:{padding:"5px 8px","margin-top":"-20px","margin-left":"5px",border:"0.5px solid #ccc","border-radius":"6px",color:"#666","font-size":"13px","margin-right":"10px",float:"right"},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.logisticstrackGo(t.orderInfo.order_sn)}}},[t._v("物流跟踪")])])],1)},d=[]}}]);