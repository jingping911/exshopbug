(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["apiMember-coupon-main"],{2658:function(t,e,i){"use strict";var a;i.d(e,"b",(function(){return n})),i.d(e,"c",(function(){return s})),i.d(e,"a",(function(){return a}));var n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("v-uni-view",[i("cu-custom",{attrs:{bgColor:"bg-white",isBack:!0}},[i("template",{attrs:{slot:"backText"},slot:"backText"},[t._v("返回")]),i("template",{staticStyle:{display:"flex","justify-content":"center"},attrs:{slot:"content"},slot:"content"},[t._v("红包")])],2),i("v-uni-view",{staticClass:"tabr",style:{top:t.headerTop}},[i("v-uni-view",{class:{on:"valid"==t.typeClass},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchType("valid")}}},[t._v("可用")]),i("v-uni-view",{class:{on:"invalid"==t.typeClass},on:{click:function(e){arguments[0]=e=t.$handleEvent(e),t.switchType("invalid")}}},[t._v("已失效")]),i("v-uni-view",{staticClass:"border",class:t.typeClass})],1),i("v-uni-view",{staticClass:"place"}),i("v-uni-view",{staticClass:"list"},[i("v-uni-view",{staticClass:"sub-list valid",class:t.subState},[0==t.couponList.length?i("v-uni-view",{staticClass:"tis"},[t._v("没有数据~")]):t._e(),t._l(t.couponList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"row"},[i("v-uni-view",{staticClass:"menu",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.deleteCoupon(e.bonus_id,t.couponList)}}},[i("v-uni-view",{staticClass:"icon shanchu"},[t._v("删除")])],1),i("v-uni-view",{staticClass:"carrier",class:["valid"==t.typeClass?t.theIndex==a?"open":t.oldIndex==a?"close":"":""],on:{touchstart:function(e){arguments[0]=e=t.$handleEvent(e),t.touchStart(a,e)},touchmove:function(e){arguments[0]=e=t.$handleEvent(e),t.touchMove(a,e)},touchend:function(e){arguments[0]=e=t.$handleEvent(e),t.touchEnd(a,e)}}},[i("v-uni-view",{staticClass:"left"},[i("v-uni-view",{staticClass:"title"},[t._v(t._s(e.type_name))]),i("v-uni-view",{staticClass:"term"},[t._v("最小商品金额为:"+t._s(e.min_goods_amount)+"元")]),i("v-uni-view",{staticClass:"term"},[t._v(t._s(e.use_start_date)+" ~ "+t._s(e.use_end_date))]),i("v-uni-view",{staticClass:"gap-top"}),i("v-uni-view",{staticClass:"gap-bottom"})],1),i("v-uni-view",{staticClass:"right"},[i("v-uni-view",{staticClass:"ticket"},[i("v-uni-view",{staticClass:"num"},[t._v(t._s(e.type_money))]),i("v-uni-view",{staticClass:"unit"},[t._v("元")])],1),i("v-uni-view",{staticClass:"criteria"}),"y"==t.is_order?i("v-uni-view",{staticClass:"use",on:{click:function(i){arguments[0]=i=t.$handleEvent(i),t.use(e.bonus_id,e.type_name,e.type_money,e.min_goods_amount)}}},[t._v("去使用")]):i("v-uni-view",{staticClass:"use"},[t._v("未失效")])],1)],1)],1)}))],2),i("v-uni-view",{staticClass:"sub-list invalid",class:t.subState},[0==t.couponList.length?i("v-uni-view",{staticClass:"tis"},[t._v("没有数据~")]):t._e(),t._l(t.couponList,(function(e,a){return i("v-uni-view",{key:a,staticClass:"row"},[i("v-uni-view",{staticClass:"menu",on:{click:function(i){i.stopPropagation(),arguments[0]=i=t.$handleEvent(i),t.deleteCoupon(e.bonus_id,t.couponList)}}},[i("v-uni-view",{staticClass:"icon shanchu"},[t._v("删除")])],1),i("v-uni-view",{staticClass:"carrier",class:["invalid"==t.typeClass?t.theIndex==a?"open":t.oldIndex==a?"close":"":""],on:{touchstart:function(e){arguments[0]=e=t.$handleEvent(e),t.touchStart(a,e)},touchmove:function(e){arguments[0]=e=t.$handleEvent(e),t.touchMove(a,e)},touchend:function(e){arguments[0]=e=t.$handleEvent(e),t.touchEnd(a,e)}}},[i("v-uni-view",{staticClass:"left"},[i("v-uni-view",{staticClass:"title"},[t._v(t._s(e.type_name))]),i("v-uni-view",{staticClass:"term"},[t._v("最小商品金额为:"+t._s(e.min_goods_amount)+"元")]),i("v-uni-view",{staticClass:"term"},[t._v(t._s(e.use_start_date)+" ~ "+t._s(e.use_end_date))]),i("v-uni-view",{staticClass:"icon shixiao"}),i("v-uni-view",{staticClass:"gap-top"}),i("v-uni-view",{staticClass:"gap-bottom"})],1),i("v-uni-view",{staticClass:"right invalid"},[i("v-uni-view",{staticClass:"ticket"},[i("v-uni-view",{staticClass:"num"},[t._v(t._s(e.type_money))]),i("v-uni-view",{staticClass:"unit"},[t._v("元")])],1),i("v-uni-view",{staticClass:"criteria"}),i("v-uni-view",{staticClass:"use"},[t._v("已失效")])],1)],1)],1)}))],2)],1)],1)},s=[]},3027:function(t,e,i){"use strict";var a=i("4ea4");i("a434"),i("a9e3"),Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0,i("96cf");var n=a(i("1da1")),s=i("b3d2"),o=i("ffcc"),r={onLoad:function(t){var e=this;this.money=t.money,this.order=t.order;var i=setInterval((function(){var t=document.getElementsByTagName("uni-page-head");t.length>0&&(e.headerTop=t[0].offsetHeight+"px",clearInterval(i))}),1)},onShow:function(){(0,s.toLogin)(),this.getCouponList(),this.order&&(this.is_order=this.order,this.costItem=this.money)},data:function(){return{couponList:[],headerTop:"50px",typeClass:"valid",subState:"showvalid",theIndex:null,oldIndex:null,isStop:!1,may_use:0,failure:0,is_order:"n",costItem:"",page:1}},onPageScroll:function(t){},onPullDownRefresh:function(){setTimeout((function(){uni.stopPullDownRefresh()}),1e3)},methods:{use:function(t,e,i,a){if(Number(this.costItem)<a)return uni.showToast({title:"不满足红包使用条件",duration:2e3,icon:"none",mask:!0,success:function(t){}}),!1;uni.setStorageSync("bonus_id",t),uni.setStorageSync("bonusName",e),uni.setStorageSync("bonusMoney",i),uni.navigateBack({url:"/apiCart/order/main"})},getCouponList:function(t){var e=this;return(0,n.default)(regeneratorRuntime.mark((function t(){var i;return regeneratorRuntime.wrap((function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,(0,o.couponListApi)({status:e.subState,openId:e.openId,page:e.page});case 2:i=t.sent,e.couponList=i.data.bonus,e.may_use=i.data.showvalid;case 5:case"end":return t.stop()}}),t)})))()},switchType:function(t){var e=this;this.typeClass!=t&&(uni.pageScrollTo({scrollTop:0,duration:0}),this.typeClass=t,this.subState=""==this.typeClass?"":"show"+t,setTimeout((function(){e.oldIndex=null,e.theIndex=null,e.subState="valid"==e.typeClass?"":e.subState}),200),this.getCouponList(t))},touchStart:function(t,e){e.touches.length>1?this.isStop=!0:(this.oldIndex=this.theIndex,this.theIndex=null,this.initXY=[e.touches[0].pageX,e.touches[0].pageY])},touchMove:function(t,e){var i=this;if(e.touches.length>1)this.isStop=!0;else{var a=e.touches[0].pageX-this.initXY[0],n=e.touches[0].pageY-this.initXY[1];this.isStop||Math.abs(a)<5||(Math.abs(n)>Math.abs(a)?this.isStop=!0:a<0?(this.theIndex=t,this.isStop=!0):a>0&&null!=this.theIndex&&this.oldIndex==this.theIndex&&(this.oldIndex=t,this.theIndex=null,this.isStop=!0,setTimeout((function(){i.oldIndex=null}),150)))}},touchEnd:function(t,e){this.isStop=!1},deleteCoupon:function(t,e){var i=this;return(0,n.default)(regeneratorRuntime.mark((function a(){var n,s,r,c;return regeneratorRuntime.wrap((function(a){while(1)switch(a.prev=a.next){case 0:return a.next=2,(0,o.deleteCouponApi)({coupon_id:t});case 2:n=a.sent,"suc"==n.data.msg&&(s=i,uni.showToast({icon:"none",title:"删除成功",success:function(){setTimeout((function(){s.getCouponList()}),1e3)}})),r=e.length,c=0;case 6:if(!(c<r)){a.next=13;break}if(t!=e[c].id){a.next=10;break}return e.splice(c,1),a.abrupt("break",13);case 10:c++,a.next=6;break;case 13:i.oldIndex=null,i.theIndex=null;case 15:case"end":return a.stop()}}),a)})))()},discard:function(){}}};e.default=r},"3fc7":function(t,e,i){"use strict";i.r(e);var a=i("3027"),n=i.n(a);for(var s in a)"default"!==s&&function(t){i.d(e,t,(function(){return a[t]}))}(s);e["default"]=n.a},"7cb0":function(t,e,i){var a=i("830d");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var n=i("4f06").default;n("240f8ab8",a,!0,{sourceMap:!1,shadowMode:!1})},"830d":function(t,e,i){var a=i("24fb");e=a(!1),e.push([t.i,'@charset "UTF-8";\n/**\n * 这里是uni-app内置的常用样式变量\n *\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\n *\n */\n/**\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\n *\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\n */\n/* 页面左右间距 */\n/* 文字尺寸 */\n/*文字颜色*/\n/* 边框颜色 */\n/* 图片加载中颜色 */\n/* 行为相关颜色 */uni-view[data-v-2c4dd702]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap}uni-page-body[data-v-2c4dd702]{position:relative;background-color:#f5f5f5}.hidden[data-v-2c4dd702]{display:none!important}.place[data-v-2c4dd702]{width:100%;\nheight:%?155?%;\n}.tabr[data-v-2c4dd702]{background-color:#fff;width:100%;height:%?95?%;padding:0 3%;border-bottom:solid %?1?% #dedede;position:fixed;top:0;z-index:10}.tabr uni-view[data-v-2c4dd702]{width:50%;height:%?90?%;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?32?%;color:#999}.tabr .on[data-v-2c4dd702]{color:#f06c7a}.tabr .border[data-v-2c4dd702]{height:%?4?%;background-color:#f06c7a;-webkit-transition:all .3s ease-out;transition:all .3s ease-out}.tabr .border.invalid[data-v-2c4dd702]{-webkit-transform:translate3d(100%,0,0);transform:translate3d(100%,0,0)}.list[data-v-2c4dd702]{width:100%;display:block;position:relative}@-webkit-keyframes showValid-data-v-2c4dd702{0%{-webkit-transform:translateX(-100%);transform:translateX(-100%)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@keyframes showValid-data-v-2c4dd702{0%{-webkit-transform:translateX(-100%);transform:translateX(-100%)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@-webkit-keyframes showInvalid-data-v-2c4dd702{0%{-webkit-transform:translateX(0);transform:translateX(0)}100%{-webkit-transform:translateX(-100%);transform:translateX(-100%)}}@keyframes showInvalid-data-v-2c4dd702{0%{-webkit-transform:translateX(0);transform:translateX(0)}100%{-webkit-transform:translateX(-100%);transform:translateX(-100%)}}.sub-list[data-v-2c4dd702]{width:100%;padding:%?20?% 0 %?120?% 0}.sub-list.invalid[data-v-2c4dd702]{position:absolute;top:%?120?%;left:100%;display:none}.sub-list.showvalid[data-v-2c4dd702]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-animation:showValid-data-v-2c4dd702 .2s linear both;animation:showValid-data-v-2c4dd702 .2s linear both}.sub-list.showinvalid[data-v-2c4dd702]{display:-webkit-box;display:-webkit-flex;display:flex;-webkit-animation:showInvalid-data-v-2c4dd702 .2s linear both;animation:showInvalid-data-v-2c4dd702 .2s linear both}.sub-list .tis[data-v-2c4dd702]{position:relative;top:%?120?%;width:100%;height:%?60?%;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?32?%}.sub-list .row[data-v-2c4dd702]{width:92%;height:24vw;margin:%?20?% auto %?10?% auto;border-radius:%?8?%;-webkit-box-align:center;-webkit-align-items:center;align-items:center;position:relative;overflow:hidden;z-index:4;border:0\n  /*\n\t\t\t<view class="carrier" :class="[theIndex==index?\'open\':oldIndex==index?\'close\':\'\']" @touchstart="touchStart(index,$event)" @touchmove="touchMove(index,$event)" @touchend="touchEnd(index,$event)">\n\t\t\t\t<view class="left">\n\t\t\t\t\t<view class="title">\n\t\t\t\t\t\t10元日常用品类\n\t\t\t\t\t</view>\n\t\t\t\t\t<view class="term">\n\t\t\t\t\t\t2019-04-01~2019-05-30\n\t\t\t\t\t</view>\n\t\t\t\t</view>\n\t\t\t\t<view class="right">\n\t\t\t\t\t<view class="ticket">\n\t\t\t\t\t\t<view class="num">\n\t\t\t\t\t\t\t10\n\t\t\t\t\t\t</view>\n\t\t\t\t\t\t<view class="unit">\n\t\t\t\t\t\t\t元\n\t\t\t\t\t\t</view>\n\t\t\t\t\t</view>\n\t\t\t\t\t<view class="criteria">\n\t\t\t\t\t\t满50使用\n\t\t\t\t\t</view>\n\t\t\t\t\t<view class="use">\n\t\t\t\t\t\t去使用\n\t\t\t\t\t</view>\n\t\t\t\t</view>\n\t\t\t</view>\n\t\t\t* \n\t\t\t* */}.sub-list .row .menu[data-v-2c4dd702]{position:absolute;width:28%;height:100%;right:0;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;background-color:#ccc;color:#fff;z-index:2}.sub-list .row .menu .icon[data-v-2c4dd702]{color:#fff;font-size:%?28?%}.sub-list .row .carrier[data-v-2c4dd702]{background-color:#fff;width:100%;padding:0 0;height:100%;z-index:3;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:nowrap;flex-wrap:nowrap;-webkit-justify-content:space-around;justify-content:space-around}@-webkit-keyframes showMenu-data-v-2c4dd702{0%{-webkit-transform:translateX(0);transform:translateX(0)}100%{-webkit-transform:translateX(-28%);transform:translateX(-28%)}}@keyframes showMenu-data-v-2c4dd702{0%{-webkit-transform:translateX(0);transform:translateX(0)}100%{-webkit-transform:translateX(-28%);transform:translateX(-28%)}}@-webkit-keyframes closeMenu-data-v-2c4dd702{0%{-webkit-transform:translateX(-28%);transform:translateX(-28%)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}@keyframes closeMenu-data-v-2c4dd702{0%{-webkit-transform:translateX(-28%);transform:translateX(-28%)}100%{-webkit-transform:translateX(0);transform:translateX(0)}}.sub-list .row .carrier.open[data-v-2c4dd702]{-webkit-animation:showMenu-data-v-2c4dd702 .25s linear both;animation:showMenu-data-v-2c4dd702 .25s linear both}.sub-list .row .carrier.close[data-v-2c4dd702]{-webkit-animation:closeMenu-data-v-2c4dd702 .15s linear both;animation:closeMenu-data-v-2c4dd702 .15s linear both}.sub-list .row .carrier .left[data-v-2c4dd702]{width:100%;position:relative}.sub-list .row .carrier .left .title[data-v-2c4dd702]{padding-top:3vw;width:90%;margin:0 5%;font-size:%?36?%}.sub-list .row .carrier .left .term[data-v-2c4dd702]{width:90%;margin:0 5%;font-size:%?26?%;color:#999}.sub-list .row .carrier .left .gap-top[data-v-2c4dd702],\n.sub-list .row .carrier .left .gap-bottom[data-v-2c4dd702]{position:absolute;width:%?20?%;height:%?20?%;right:%?-10?%;border-radius:100%;background-color:#f5f5f5}.sub-list .row .carrier .left .gap-top[data-v-2c4dd702]{top:%?-10?%}.sub-list .row .carrier .left .gap-bottom[data-v-2c4dd702]{bottom:%?-10?%}.sub-list .row .carrier .left .shixiao[data-v-2c4dd702]{position:absolute;right:%?20?%;font-size:%?150?%;z-index:6;color:hsla(0,0%,60%,.2)}.sub-list .row .carrier .right[data-v-2c4dd702]{-webkit-flex-shrink:0;flex-shrink:0;width:28%;color:#fff;background:-webkit-linear-gradient(left,#ec625c,#ee827f);background:linear-gradient(90deg,#ec625c,#ee827f);-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center}.sub-list .row .carrier .right.invalid[data-v-2c4dd702]{background:-webkit-linear-gradient(left,#aaa,#999);background:linear-gradient(90deg,#aaa,#999)}.sub-list .row .carrier .right.invalid .use[data-v-2c4dd702]{color:#aaa}.sub-list .row .carrier .right .ticket[data-v-2c4dd702],\n.sub-list .row .carrier .right .criteria[data-v-2c4dd702]{width:100%}.sub-list .row .carrier .right .ticket[data-v-2c4dd702]{padding-top:1vw;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:baseline;-webkit-align-items:baseline;align-items:baseline;height:6vw}.sub-list .row .carrier .right .ticket .num[data-v-2c4dd702]{font-size:%?42?%;font-weight:600}.sub-list .row .carrier .right .ticket .unit[data-v-2c4dd702]{font-size:%?24?%}.sub-list .row .carrier .right .criteria[data-v-2c4dd702]{-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;font-size:%?28?%}.sub-list .row .carrier .right .use[data-v-2c4dd702]{width:50%;height:%?40?%;-webkit-box-pack:center;-webkit-justify-content:center;justify-content:center;-webkit-box-align:center;-webkit-align-items:center;align-items:center;font-size:%?24?%;background-color:#fff;color:#ee827f;border-radius:%?40?%;padding:0 %?10?%}body.?%PAGE?%[data-v-2c4dd702]{background-color:#f5f5f5}',""]),t.exports=e},"93c4":function(t,e,i){"use strict";var a=i("7cb0"),n=i.n(a);n.a},f1bf:function(t,e,i){"use strict";i.r(e);var a=i("2658"),n=i("3fc7");for(var s in n)"default"!==s&&function(t){i.d(e,t,(function(){return n[t]}))}(s);i("93c4");var o,r=i("f0c5"),c=Object(r["a"])(n["default"],a["b"],a["c"],!1,null,"2c4dd702",null,!1,a["a"],o);e["default"]=c.exports}}]);