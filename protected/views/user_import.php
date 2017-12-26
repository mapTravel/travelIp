<?php require __DIR__ . '/_header.php' ?>
<?php require __DIR__ . '/_sidebar.php' ?>

	<div class="content-wrapper" style="min-height: 914px;">
		<section class="content-header">
			<h1>
				Импорт из файла
				<small>csv</small>
			</h1>
		</section>

		<section class="content" ng-controller="UserCsvCtrl">
			<div class="col-md-11">
				<div class="box box-success">
					<div class="box-body">
						<?php if(is_array($files) && !empty($files)):?>
							<table class="table table-hover">
								<thead>
								<tr>
									<th>Файл</th>
									<th>Дата загрузки</th>
									<th>Статус</th>
									<th></th>
								</tr>
								</thead>
								<tbody>
								<?php foreach($files as $f):?>
									<tr>
										<td><?= $f['filename'];?></td>
										<td><?= $f['time'];?></td>
										<td><span ng-init="statusMsg = '<?= $f["status"];?>'" ng-class="status" class="label <?= ($f['status'] == 'No' ? 'label-danger': 'label-success') ?>">{{statusMsg}}</span></td>
										<td>
											<?php if($f['status'] == 'No'):
												$f = $f['filename'];
												?>
												<button ng-disabled="importBtn" ng-click="import('<?=$f;?>')" style='width: 100px;float: right;' class='btn btn-block btn-success'><i class='fa fa-fw fa-plus'></i> Импорт</button>
											<?php endif;?>
										</td>
									</tr>
								<?php endforeach;?>

								</tbody>
							</table>
						<?php endif;?>
						<?php if(empty($files)):?>
							<div class="callout callout-info">
								<h4>Файлов в папке csv нет</h4>
							</div>
						<?php endif;?>
					</div>
				</div>
			</div>
		</section>
	</div>
<?php require __DIR__ . '/_footer.php' ?>