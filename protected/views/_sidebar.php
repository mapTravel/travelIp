<aside class="main-sidebar">
	<section class="sidebar" style="height: auto;">

		<ul class="sidebar-menu">
			<li class="<?php echo ($this->request->getPathInfo() == '/') ? 'active' : '';?>">
				<a href="<?php echo $this->router->generate('map')?>">
					<i class="fa fa-fw fa-map-o"></i>
					<span>Карта путешествий</span>
				</a>
			</li>
			<li class="<?php echo ($this->request->getPathInfo() == '/user_import') ? 'active' : '';?>">
				<a href="<?php echo $this->router->generate('user_import')?>">
					<i class="fa fa-fw fa-file-text"></i>
					<span>Импорт из файла</span>
				</a>
			</li>
		</ul>
	</section>

</aside>