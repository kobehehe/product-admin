<style type="text/css">
	html,
	body {
		width: 100%;
		/* height: 100%; */
	}

	.app .menu {
		width: 150px;
	}

	.app {
		display: flex;
	}

	.app .content {
		flex: 1;
		display: flex;
		flex-direction: column;
		padding-left: 10px;
	}

	.order-id {
		width: 200px;
		margin-right: 20px;
	}

	.content-header {
		background: #fbfbfb;
		padding: 20px;
		border-radius: 20px;
	}

	.el-tab-pane {
		display: none !important;
	}

	.el-form-item__label {
		width: 100px !important;
	}

	.controller {
		/* padding-left: 30px; */
	}

	.el-menu-vertical-demo,
	.el-menu {
		border: none;
	}

	.container {
		width: 80%;
		min-width: 1300px;
		margin-bottom: 50px;
	}

	.el-menu-item {
		height: 30px;
		line-height: 30px;
	}

	.label {
		margin: 0;
	}
</style>
<div class="container top">

	<input type="hidden" id="exporturl" value=" <?php echo site_url('admin') .'/orders/exportorder';?> ">
	<input type="hidden" id="uploadurl" value=" <?php echo site_url('admin') .'/orders/uploadorder/' ?> ">
	<input type="hidden" id="delivery_url" value=" <?php echo site_url('admin') .'/auth/delivery/' ?> ">
	<input type="hidden" id="deliveryone_url" value=" <?php echo site_url('admin') .'/auth/deliveryone/' ?> ">
	<input type="hidden" id="delete_url" value=" <?php echo site_url('admin') .'/auth/deleteone/' ?> ">
	<input type="hidden" id="list_url" value=" <?php echo site_url('admin') .'/orders' ?> ">
	<input type="hidden" id="getOrder_url" value=" <?php echo site_url('admin') .'/orders' ?> ">

	<ul class="breadcrumb">
		<li>
			<a href="<?php echo site_url(" admin "); ?>">
				<?php echo '后台管理'; ?>
			</a>
			<span class="divider">/</span>
		</li>
		<li class="active">
			<?php echo '订单管理'; ?>
		</li>
	</ul>
	<div class="app">
		<div class="menu">
			<!-- $logistics = [0=>'all',1=>'未导入',2=>'已导入']; -->
			<el-radio-group v-model="post.select" size="mini" @change='post.page=1;getOrderList();scrollTop();isGetOrder=false;'>
				<el-radio-button :label="n.type" :key='n.type' v-for='n,key in orderList.menuList'>{{key}}
					<span v-if='key!="全部订单"'>({{n.len}})</span>
				</el-radio-button>
			</el-radio-group>
			<input type="text" :value='copyIds' id='copyId'>
		</div>
		<div class="content">
			<div class="content-header">
				<el-form label-width="80px" :model="post" size="mini">
					<el-form-item label="订单id：">
						<el-input v-model="post.search_string" class='order-id' @keyup.enter='onSubmit'> </el-input>
						<el-button type="primary" @click="onSubmit">查询</el-button>
					</el-form-item>
					<el-form-item label="下单时间：">
						<el-date-picker v-model="post.time" type="daterange" align="right" format="yyyy 年 MM 月 dd 日" value-format="timestamp" unlink-panels
						    range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期" :picker-options="pickerOptions" @change='timeChange'>
						</el-date-picker>
					</el-form-item>
					<el-form-item label="店铺：">
						<el-radio-group v-model="post.manufacture_id" size="mini" @change='post.page=1;getOrderList();scrollTop();isGetOrder=false;'>
							<el-radio-button label="0">全部</el-radio-button>
							<el-radio-button :label="n.shop_id" :key='n.id' v-for='n in orderList.manufactures'>{{n.name}} ({{n.len}})<span v-if='isGetOrder' style='color:red;'> ({{n.newOrder}})</span></el-radio-button>
						</el-radio-group>
					</el-form-item>
				</el-form>
			</div>
			<div class="controller">
					<div v-if="imports!=2" class='ctrl1'>
						<el-button type="primary" size="mini" @click='deleteSomeOrder' v-if='imports!=3'>搁置</el-button>
						<el-button type="primary" size="mini" @click='noSomeStock' v-if='imports==1|| imports==0'>缺货</el-button>
						<el-button type="primary" size="mini" @click='haveSomeStock' v-if='imports!=1'>恢复</el-button>
						<el-button type="primary" size="mini" @click='sendOrder' >发货</el-button>
						<!-- v-if='imports==1|| imports==0' -->
					</div>
					<div class='ctrl2'>
						<el-button type="primary" size="mini" @click='getOrder' :loading="loading" v-if='imports==1||imports==4||imports==0'>{{loading? '拉取中':'拉取订单'}}</el-button>
						<a href="javascript:;" class="el-button el-button--primary el-button--mini file"  v-if='imports==1||imports==4||imports==0'>
							上传excel更新物流
							<input type="file" name="" id="uploadOrder" @change="fileuploaduserpic">
						</a>
						<el-button type="primary" size="mini" @click='downExcel(false)'>导出所有结果</el-button>
						<el-button type="primary" size="mini" @click='downExcel(true)'>导出选定结果</el-button>
					</div>
				</div>
			<div class="content-table">
				<div class="table table-bordered table-condensed">

					<ul class='tops'>
						<li class='header first positionit' style="min-width:90px">
							<el-checkbox v-model="isCk" @change='checkboxChange' class=''>缩略图</el-checkbox>
						</li>
						<li class="header" style="max-width:170px;min-width:170px;">地址信息</li>
						<li class="header" style="min-width:120px">留言备注</li>
						<li class="header" style="max-width:230px;">物流</li>
						<li class="header" style="max-width:120px">操作</li>
					</ul>
					<div class='scroll scrolls'>
						<div v-for='n,key in orderList.orders' :key='key' v-if='n.length!=0'>
							<ul class='tops' style="border-top: 10px solid #eaeaea; font-size:15px;">
								<li class='header first positionit ' style="min-width:90px;">
									<span class='span-checkbox'>
										<input type="checkbox" :name='n[0].order_id+","+n[0].import+","+n[0].tracking_code' class='item-checkbox'>
									</span>
									<el-tooltip class="positionit-item" :content='orderList.shopid2name[n[0].shop_id]' effect="dark" placement="top-start">

										<span @click='copyId(n[0].order_id)'>{{n[0].order_id}}</span>

									</el-tooltip>

								</li>
								<li class="header positionit" style="max-width:170px;min-width:170px;">
									<el-tooltip class="positionit-item" effect="dark" placement="top-start">
										<div slot="content" style='width:200px;' v-html='content(n[0])'></div>
										<span>{{n[0].country | langfl }}： 
											<span> {{n[0].name}}</span>
										</span>
									</el-tooltip>

								</li>
								<li class="header positionit icon-imgs" style="min-width:120px">
							
									
									<p style='font-size:15px; color:red;'>
										<b v-if='n[0].is_gift!=0'> <img src="/assets/img/gif.png" alt="">礼品包装 </b>
										<b v-if='n[0].message_from_buyer'> 
											<img src="/assets/img/message.png" alt="">
											{{n[0].message_from_buyer}} 
										</b>
										<b> {{n[0].message_from_seller}} </b>
									</p>
								</li>
								<li class="header positionit icon-imgs" style="max-width:230px;">
									<span>{{n[0].creatdTime | timefl}}下单; </span>
									<el-tooltip class="item" effect="dark" placement="top-start">
										<div slot="content" style=''>

											<span>{{n[0].carrier_name}}:{{n[0].tracking_code}}</span>
										</div>
										<span :style='{color:n[0].import==4 ? "red":""}'>{{n[0].import | state}};</span>
									</el-tooltip>
									<span style='' id='itme-info'>耗时：<i class='consuming' :class='endTimeClass(n[0])'>{{endTime(n[0])}}</i>天</span>
								</li>
								<li class="header positionit" style="max-width:120px">
									<div v-if='n[0].import!=2' class='btns'>
										<span type="primary" size="mini" @click='editOrder(n[0]);dialogVisibleEditOrder=true'>编辑</span>
										<span type="primary" size="mini" @click='sendOneOrder(n[0].order_id)' v-if='n[0].import!=3'>发货</span>
										<span type="primary" size="mini" @click='recoverysOrders([{order_id:n[0].order_id}])' v-if='n[0].import==3'>恢复</span>
										<span type="primary" size="mini" @click='deleteOrder(n[0].order_id)' v-else>搁置</span>
									</div>
								</li>
							</ul>
							<ul v-for=' i,index in n ' class='order-list' :key='index'>
								<li class='first' style="min-width:90px">
									<div class='imgs'>
										<!-- <el-tooltip class="item" effect="dark" placement="top-start">
											 <div slot="content" style='width:200px;'>{{n[0].listings_title}}</div>
											
										</el-tooltip> -->
										<img :src="imgs(i.product_img)">
										<!-- <img class="lazy" :data-original=""> -->
										<!-- <img v-lazy="imgs(i.product_img)" >
										<img v-lazy="imgs(i.product_img)" class='imgs2'/> -->
										<img :src="imgs(i.product_img)" :alt="i.listings_title" :title='i.listings_title' class='imgs2'>
									</div>
								</li>
								<li class='two'>
									<div>
										<span>
											<span v-html='i.formatted_name_a'></span>：
											<b v-html='i.formatted_value_a'></b>
										</span>
									</div>
									<div>
										<span>
											<span v-html='i.formatted_name_b'></span>：
											<b v-html='i.formatted_value_b'></b>
										</span>
									</div>
									<div class='num'>
										<span v-html='i.listings_sku'></span> X
										<span :style='{background:i.number>1 ? "red":"" }' class='pre'>
											<b v-html='i.number'></b>
										</span>
									</div>

								</li>
							</ul>
						</div>
					</div>

				</div>

			</div>

			<el-pagination style=' padding-bottom: 0px;display: flex;justify-content: flex-end;' :page-size="100" layout="prev, pager, next"
			    @current-change='pageChange' :total="orderList.count_orders" :current-page.sync='post.page'>
			</el-pagination>
			<el-dialog title="Excel上传" :visible.sync="dialogVisible" width="650px" :close-on-click-modal='false'>
				<div>
					<el-progress :text-inside="true" :stroke-width="18" :percentage="percentage" status="success"></el-progress>
					<p v-if='updateExcelRes.data.length==0' style='padding:20px 0;'>订单更新中...请稍等</p>
					<div v-else>
						<p style='padding:20px 0;'>
							<span>提交{{updateExcelRes.data.length}}个订单，</span>
							<span>成功更新{{updateExcelRes.data.length-updateExcelRes.err}}个订单，</span>
							<span>失败订单{{updateExcelRes.err}}，</span>
							<span>其中信息不全包含{{updateExcelRes.err1}}条</span>
						</p>
						<ul class='scroll'>
							<li v-for='n in updateExcelRes.data'>
								<span>单号:{{n[0].order_id}},</span>
								<span v-if='n[0].len'>共{{n[0].len}}个子单,</span>
								<span :style='{color:((n[0].res==0||n[0].res==-1) ? "#f56c6c":"#67c23a")}'>{{n[0].res | resflt }} ,</span>
								<span v-if='n[0].res==0||n[0].res==-1||n[0].msg!=""'>
									错误信息:
									<b>{{n[0].res | resflt2}} {{n[0].msg}}</b>
								</span>
							</li>
						</ul>
					</div>

				</div>
				<span slot="footer" class="dialog-footer">
					<el-button type="primary" @click="dialogVisible = false;updateExcelRes.data=[]">确 定</el-button>
				</span>
			</el-dialog>

			<el-dialog title="编辑" :visible.sync="dialogVisibleEditOrder" width="400px" :close-on-click-modal='false'>
				<el-form label-width="80px" :model="editPost" size="mini">
					<el-form-item label="姓名：">
						<el-input v-model="editPost.name" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="地址1：">
						<el-input v-model="editPost.first_line" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="地址2：">
						<el-input v-model="editPost.second_line" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="城市：">
						<el-input v-model="editPost.city" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="省/州：">
						<el-input v-model="editPost.state" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="邮编：">
						<el-input v-model="editPost.zip" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="国家：">
						<el-input v-model="editPost.country" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="电话/手机：">
						<el-input v-model="editPost.phone" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="物流商：">
						<el-input v-model="editPost.carrier_name" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="跟踪号：">
						<el-input v-model="editPost.tracking_code" class='order-id'> </el-input>
					</el-form-item>
					<el-form-item label="买家留言：">
						<el-input v-model="editPost.message_from_buyer" class='order-id' type='textarea'> </el-input>
					</el-form-item>
					<el-form-item label="备注：">
						<el-input v-model="editPost.message_from_seller" class='order-id' type='textarea'> </el-input>
					</el-form-item>
				</el-form>
				<span slot="footer" class="dialog-footer">
					<el-button type="primary" @click="dialogVisibleEditOrder = false;editPost={}">取 消</el-button>
					<el-button type="primary" @click="editOrderSb">确 定</el-button>
				</span>
			</el-dialog>
		</div>

	</div>
	<div>

		<script type="text/javascript">
		//  Vue.use(VueLazyload)
			new Vue({
				el: '.app',
				data: {
					isCk: false,
					loading: false,
					dialogVisible: false,
					dialogVisibleEditOrder: false,
					isGetOrder:false,
					copyIds: '',
					logs:'',
					imports:'',
					currentPage: 1,
					updateExcelRes: {
						data: []
					},
					editPost: {},
					percentage: 0,
					pickerOptions: {
						shortcuts: [{
							text: '最近一周',
							onClick(picker) {
								const end = new Date();
								const start = new Date();
								start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
								picker.$emit('pick', [start, end]);
							}
						}, {
							text: '最近一个月',
							onClick(picker) {
								const end = new Date();
								const start = new Date();
								start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
								picker.$emit('pick', [start, end]);
							}
						}, {
							text: '最近三个月',
							onClick(picker) {
								const end = new Date();
								const start = new Date();
								start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
								picker.$emit('pick', [start, end]);
							}
						}]
					},
					activeName: "",
					post: {

						manufacture_id: 0, //店铺id
						logistics_id: 1, //物流/[0=>'all',1=>'未发货',2=>'已发货',3=>'已取消
						status: 1, //[0=>'all',1=>'未发货',2=>'已发货',3=>'已取消',4=>缺货];
						search_string: '', //订单id
						page: 1,
						select: '1,1',
						time: [],
						// order:'',
						// order_type:'Desc'

					},
					orderList: {},
					obj: {}
				},
				created() {
					this.getOrderList()
				},
				methods: {
					copyId(key) {
						this.copyIds = key
						setTimeout(() => {
							var id = document.getElementById("copyId");
							id.select(); // 选择对象
							document.execCommand("Copy"); // 执行浏览器复制命令
							this.message('id:' + key + '已复制', 1)
						}, 100);
					},
					imgs(e) {
						return e.replace('il_170x135', 'il_300x300')
					},
					content(i) {
						return `姓名：${i.name}<br/>
						电话：${i.phone}
						<br/>国家：${i.country}
						<br/>省/州：${i.state}
						<br/>地址1：${i.first_line}
						<br/>地址2：<span v-if='i.second_line!=''&&i.second_line!="null"'>${i.second_line}</span>	
						<br/>zip：${i.zip}`
					},
					checkboxChange(e) {
						var arr = []
						$('.item-checkbox').each((i) => {
							if (e) {
								$('.item-checkbox').eq(i).attr("checked", 'checked')

							} else {
								$('.item-checkbox').eq(i).attr("checked", false)
							}

						})
						// this.selsecArr=arr
					},
					noSomeStock() {

						this.handleAlert('确认缺货吗？', (b) => {
							if (b) {
								var arr = []
								$('.item-checkbox:checked').each((i) => {
									console.log($('.item-checkbox:checked').eq(i))
									var item = $('.item-checkbox:checked').eq(i).attr("name").split(',')
									if (item[1] == 2 || item[1] == 4 || item[1] == 3) {
										return
									} //缺货逻辑,新订单时可以缺货
									if (item[2]) return
									arr.push({
										order_id: item[0]
									})
								})
								console.log(arr)
								if (arr.length) {
									this.noStock(arr)
								} else {
									this.message('请选择正确订单进行缺货')
								}
							}

						})


					},
					haveSomeStock() {
						this.handleAlert('确认恢复？', (b) => {
							if (b) {
								var arr = []
								$('.item-checkbox:checked').each((i) => {
									var item = $('.item-checkbox:checked').eq(i).attr("name").split(',')
									if (item[1] != 4 && item[1]!=3) {
										return
									} //缺货逻辑,新订单时可以缺货
									arr.push({
										order_id: item[0]
									})
								})
								if (arr.length) {
									this.recoverysOrders(arr)
								} else {
									this.message('请选择正确订单进行恢复')
								}
							}
						})


					},
					deleteSomeOrder() {
						this.handleAlert('确认搁置订单？', (b) => {
							if (b) {
								var arr = []
								$('.item-checkbox:checked').each((i) => {
									var item = $('.item-checkbox:checked').eq(i).attr("name").split(',')
									if (item[1] == 2 || item[1] == 3) {
										return
									} //搁置逻辑,除了 已发货 和 已经搁置
									arr.push({
										order_id: item[0]
									})
								})
								if (arr.length) {
									var _this = this
									$.ajax({
										url: '/admin/auth/deleteones/',
										type: 'POST',
										data: {
											arr: arr
										},
										timeout: 600000,
										dataType: 'json',
										success: function (responseStr) {
											if (responseStr.code == 0) {
												_this.message('搁置订单成功', 1)
												_this.getOrderList()
												return;
											} else {
												_this.message('搁置订单失败', 0)
												return;
											}
										}
									});
								} else {
									this.message('请选择正确订单进行搁置')
								}
							}
						})


					},
					recoverysOrders(e) {
						var _this = this
						$.ajax({
							url: '/admin/orders/recoverys',
							type: 'POST',
							data: {
								arr: e
							},
							timeout: 600000,
							dataType: 'json',
							success: function (res) {
								if (res.code) {
									_this.message(res.message, 1)
									_this.getOrderList()
								} else {
									_this.message(res, 0)
								}
							}
						});
					},
					noStock(e) {
						var _this = this
						$.ajax({

							url: '/admin/orders/noStock',
							type: 'POST',
							data: {
								arr: e
							},
							timeout: 600000,
							dataType: 'json',
							success: function (res) {
								if (res.code) {
									_this.message(res.message, 1)
									_this.getOrderList()
								} else {
									_this.message(res, 0)
								}
							}
						});
					},
					timeChange(e) {
						if (e == null) {
							this.post.time = []
						}
						this.post.page = 1;
						this.getOrderList();
						this.scrollTop();
					},
					editOrder(e) {
						this.editPost = JSON.parse(JSON.stringify(e))
					},
					editOrderSb() {
						var _this = this
						var post = this.editPost
						for (var i in post) {
							if (post[i] == '' || post[i] == null || post[i] == undefined) {
								post[i] = ""
							}
						}
						$.ajax({
							url: '/admin/orders/update/',
							type: 'POST',
							data: post,
							timeout: 600000,
							dataType: 'json',
							success: function (responseStr) {
								_this.dialogVisibleEditOrder = false;
								if (responseStr.code == 1) {
									_this.message('修改成功', 1);
									_this.getOrderList()
									return;
								} else {
									_this.message('修改失败', 0);
									return;
								}
							}
						});
					},
					message(msg, state) {
						state = state ? 'success' : 'error'
						time = state ? 10000 : 60000
						// this.$message({
						// 	message: msg,
						// 	type: state
						// });
						this.$notify({
							message: msg,
							duration: time,
							type: state
						});
					},
					pageChange(e) {
						this.post.page = e
						this.getOrderList()
						this.scrollTop()
					},
					scrollTop() {
						$('.scrolls').scrollTop(0)
					},
					menuSelect(e) {
						console.log(e)
					},
					deleteOrder(e) { //取消订单
						this.handleAlert('确认搁置该订单？', (b) => {
							if (b) {
								var url = $("#delete_url").val();
								var _this = this
								$.ajax({
									url: url,
									type: 'POST',
									data: {
										oid: e,
										type: 3
									},
									timeout: 600000,
									dataType: 'json',
									success: function (responseStr) {
										if (responseStr.code == 0) {
											_this.message('搁置订单成功', 1)
											_this.getOrderList()
											return;
										} else {
											_this.message('搁置订单失败', 0)
											return;
										}
									}
								});
							}
						})

					},
					sendOneOrder(e) {
						this.handleAlert('确认发货吗？', (b) => {

							if (b) {
								var loading = this.$loading({
									lock: true,
									text: '发货中..',
									spinner: 'el-icon-loading',
									background: 'rgba(, 0, 0, 0.5)'
								});
								var urlone = $("#deliveryone_url").val();
								var _this = this
								$.ajax({
									url: urlone,
									type: 'POST',
									data: {
										oid: e
									},
									timeout: 600000,
									dataType: 'json'
								}).then((res) => {
									if (res.code == 1) {
										_this.message('成功发货该订单', 1)
										_this.getOrderList()
										loading.close()
										return;
									} else {
										_this.message('发货失败', 0)
										loading.close()
										return;
									}

								}, (err) => {
									loading.close()
								})
							}
						})


					},
					recover(e) { //恢复订单
						this.handleAlert('确认恢复吗？', (b) => {
							if (b) {
								var url = $("#delete_url").val();
								var _this = this

								$.ajax({
									url: url,
									type: 'POST',
									data: {
										oid: e,
										type: 1
									},
									timeout: 600000,
									dataType: 'json',
									success: function (responseStr) {
										if (responseStr.code == 0) {
											_this.message('恢复成功', 1)
											_this.getOrderList()
										} else {
											_this.message('恢复失败', 0)
											_this.getOrderList()
											return;
										}
									}
								});
							}
						})

					},
					getOrder() { //
						this.loading = true
											
						//拉取订单
						var _this = this
						var loading = this.$loading({
							lock: true,
							text: '订单拉取中..',
							spinner: 'el-icon-loading',
							background: 'rgba(, 0, 0, 0.5)'
						});

						$.ajax({
							type: "get",
							url: "/admin/auth/pullorder",
							async: true,
							timeout: 600000,
						}).then(function (res) {
							console.log(res)
							if (res) {
								_this.loading = false
								_this.isGetOrder = true	
								if(res.error.length){

									_this.message('成功拉取' + res.num + '个订单' + '但' +res.error.join(' , ')+'授权失败', 0);
								}else{
									_this.message('成功拉取' + res.num + '个订单', 1);
								}
								
								loading.close()
								// _this.message('成功拉取订单', 1);
								_this.getOrderList()
							}

						}, function (err) {
							loading.close()
						})
					},
					sendOrder() {
						//发货
						var arr = []
						$('.item-checkbox:checked').each((i) => {
							var item = $('.item-checkbox:checked').eq(i).attr("name").split(',')
							arr.push(item[0])
						})
						if (arr.length == 0) {
							this.message('请选择订单发货', 0)
							return
						}

						this.handleAlert('确认发货吗？', (b) => {
							if (b) {
								var url = $("#delivery_url").val();
								var _this = this
								var loading = this.$loading({
									lock: true,
									text: '发货中..',
									spinner: 'el-icon-loading',
									background: 'rgba(, 0, 0, 0.5)'
								});
								$.ajax({
									url: url,
									type: 'POST',
									timeout: 600000,
									data: {
										arr
									}
								}).then((res) => {
									if (res.code == 1) {
										if (res.num == res.res) {
											_this.message('成功发货' + res.num + '个订单', 1)
										} else {
											_this.message('共发货' + res.num + '个订单,成功' + res.res + '个', 0)
										}
										_this.getOrderList()
										loading.close()
										return;
									} else {
										_this.message('发货失败', 0)
										loading.close()
										return;
									}
								}, (err) => {
									loading.close()
								})
							}
						})
					},
					downExcel(e) { //下载excel  TODO  //后端bug 
						var query = [];
						if (e) {
							$('.item-checkbox:checked').each((i) => {
								var item = $('.item-checkbox:checked').eq(i).attr("name").split(',')
								query.push(item[0])
							})
							if (query.length == 0) {
								this.message('请选择需要导出的订单', 0)
								return
							}
						}

						this.handleAlert('确认导出订单？', (b) => {
							if (b) {
								var post = this.post
								var orderid = post.search_string;
								var shopid = post.manufacture_id;
								var logistics = post.select.split(',')[1]; //物流状态
								var impt = post.select.split(',')[0]; //订单状态
								var time = post.time;


								$.ajax({
									type: "post",
									url: "/admin/orders/exportorder",
									async: true,
									data: {
										orderid,
										shopid,
										logistics,
										impt,
										query,
										time
									},
									timeout: 600000
								}).then(function (res) {
									if (res.code) {
										window.location.href = res.path;
									} else {
										_this.message(res.path, 0)
									}

								})
							}
						})
						// 下载excel
					},
					getOrderList: function (option) {
						var old = JSON.parse(JSON.stringify(this.orderList))
						this.imports = this.post.select.split(',')[0];
						this.logs = this.post.select.split(',')[1];
						var _this = this
						var post = JSON.parse(JSON.stringify(this.post))
						var loading = this.$loading({
							lock: true,
							text: '数据加载中..',
							spinner: 'el-icon-loading',
							background: 'rgba(, 0, 0, 0.5)'
						});

						if (option === true) {
							for (var i in post) {
								if (i != 'search_string') {
									post[i] = 0
								}
							}
							post['page'] = 1

						} else {
							this.post.search_string = ''
							post['search_string'] = ''
							post['status'] = post.select.split(',')[0]
							post['logistics_id'] = post.select.split(',')[1]

						}

						// status	logistics_id: 0, //物流/[0=>'all',1=>'未发货',2=>'已发货',3=>'已取消

						$.ajax({
							type: "post",
							url: "/admin/ordersList",
							async: true,
							data: post,
							timeout: 600000,
						}).then(function (res) {
							// if(this.isGetOrder){
								if(old.manufactures!=undefined){
								for(var i in res.manufactures){	
									res.manufactures[i].newOrder=res.manufactures[i].len- old.manufactures[i].len
								}
							}
							// }

							
							console.log(res)
							res.orders = uniq(res.orders)
							_this.orderList = res
							_this.isCk = false
							$('.item-checkbox').each((i) => {

								$('.item-checkbox').eq(i).attr("checked", false)



							})
							loading.close()

						}, function (err) {
							loading.close()

						})
					},
					fileuploaduserpic: function () {

						var _this = this
						var filefullpath = $('#uploadOrder').val();
						if (filefullpath == '') {
							return;
						}
						var filetype = filefullpath.substring(filefullpath.lastIndexOf(".") + 1, filefullpath.length);
						var validfiletype = 'xls';
						var validfiletypearr = validfiletype.split(',');
						filetype = filetype.toLowerCase();
						if ($.inArray(filetype, validfiletypearr) == -1) {
							_this.message('文件类型不支持，请另存为 xls 格式！', 0);
							$('#uploadOrder').val('')
							return;
						}
						this.dialogVisible = true
						var formData = new FormData();
						formData.append("file", $("#uploadOrder")[0].files[0]);
						var url = $("#uploadurl").val();
						var xhrOnProgress = function (fun) {
							xhrOnProgress.onprogress = fun; //绑定监听
							//使用闭包实现监听绑
							return function () {
								//通过$.ajaxSettings.xhr();获得XMLHttpRequest对象
								var xhr = $.ajaxSettings.xhr();
								//判断监听函数是否为函数
								if (typeof xhrOnProgress.onprogress !== 'function')
									return xhr;
								//如果有监听函数并且xhr对象支持绑定时就把监听函数绑定上去
								if (xhrOnProgress.onprogress && xhr.upload) {
									xhr.upload.onprogress = xhrOnProgress.onprogress;
								}
								return xhr;
							}
						}
						$.ajax({
							url: url,
							type: 'POST',
							data: formData,
							dataType: 'json',
							processData: false, // 告诉jQuery不要去处理发送的数据
							contentType: false, // 告诉jQuery不要去设置Content-Type请求头
							timeout: 600000,
							beforeSend: function () {
								console.log("正在进行，请稍候");
							},
							xhr: xhrOnProgress(function (e) {
								var percent = e.loaded / e.total * 100; //计算百分比
								_this.percentage = percent
							}),
							success: function (res) {
								_this.updateExcelRes = res
								_this.getOrderList()
								$('#uploadOrder').val('')
							},
							error: function () {
								$('#uploadOrder').val('')
							}
						});
					},
					onSubmit: function () {
						this.post.page = 1
						this.getOrderList(true)

					},
					handleClick: function () {

					},
					toggleSelection(rows) {
						if (rows) {
							rows.forEach(row => {
								this.$refs.multipleTable.toggleRowSelection(row);
							});
						} else {
							this.$refs.multipleTable.clearSelection();
						}
					},
					handleSelectionChange(val) {
						this.multipleSelection = val;
					},

					handleAlert(message, callback) {
						this.$confirm(message, '提示', {
							confirmButtonText: '确定',
							cancelButtonText: '取消'
						}).then(() => {
							callback(true)
						}).catch(() => {
							callback(false)
						});
					},
					endTime(e){
						var time =0
						
						if(this.imports==2){
							 time =  ((e.endTime - e.creatdTime)/60/60/24).toFixed(0);
							
						}else{
							 time = ((new Date().getTime()/1000 - e.creatdTime)/60/60/24).toFixed(0);
				
						}
						return time ;
						
					},
					endTimeClass(e){
						var className = ''
						var time=0
						if(this.imports==2){
							 time =  ((e.endTime - e.creatdTime)/60/60/24).toFixed(0);
							
						}else{
							 time = ((new Date().getTime()/1000 - e.creatdTime)/60/60/24).toFixed(0);
				
						}
						
	
							if(Math.abs(time)>=3){
								className = 'three'
							}
							if(Math.abs(time)>=7){
								className = 'seven'
							}
							
			


						return className;
						

					}


				},
				watch: {

				},
				filters: {
					state: function (e) {
						switch (e) {
							case '1':
								return '待发货';
								break;
							case '2':
								return '已发货';
								break;
							case '3':
								return '已取消';
								break;
							case '4':
								return '缺货';
								break;
						}
					},
					resflt(e) {
						if (e > 0) {
							return '更新成功'
						}
						switch (e) {
							case -1:
								return '更新失败';
								break;
							case 1:
								return '更新成功';
								break;
							case 0:
								return '更新失败';
								break;
						}
					},
					resflt2(e) {
						switch (e) {
							case -1:
								return '数据库无此订单号';
								break;
							case 0:
								return '写入数据与数据库一致';
								break;
						}
					},
					timefl(e) {
						return new Date(e * 1000).Format("MM-dd");
					},
					langfl(e) {
						return enTranslationZh(e)
					}

				}

			})
		</script>