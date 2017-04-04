

		<?php
		if( isset( $block ) ) {
			$ad_name = ( $block == 1 ) ? 'top1' : 'pano' . $block;
			if( $block == 1 ) {
				echo "
				<div class='col-sm-12'>
					<div class='ad-wrapper'>
						<div class='ad-text'>Annons:</div>";

				do_ad( array( 'name' => "Top2" /**"l_top1"*/, 'width' => '960', 'class' => 'hidden-xs' ) );
				do_ad( array( 'name' => "m_Top1" /**"s_top1"*/, 'width' => '320', 'class' => 'visible-xs' ) );

				echo "
					</div>
				</div>";
			} else {
				echo "
				<div class='col-sm-12 visible-xs'>
					<div class='ad-wrapper'>
						<div class='ad-text'>Annons:</div>";
				
				do_ad( array( 'name' => "_none_" /**"l_pano$block"*/, 'width' => '960', 'class' => 'hidden-xs' ) );
				do_ad( array( 'name' => "m_Middle1" /**"s_pano$block"*/, 'width' => '320', 'class' => 'visible-xs' ) );

				echo "
					</div>
				</div>";
			}
		} else {
			echo "
			<div class='col-sm-12'>
				<div class='ad-wrapper'>
					<div class='ad-text'>Annons:</div>";

			do_ad( array( 'name' => "Top2" /**"l_top1"*/, 'width' => '960', 'class' => 'hidden-xs' ) );
			do_ad( array( 'name' => "m_Top1" /**"s_top1"*/, 'width' => '320', 'class' => 'visible-xs' ) );

			echo "
				</div>
			</div>";
		}

		?>

	