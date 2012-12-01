<?php

class LalrHTMLVisualizationGenerator {
	private $name;
	private $fsm;
	private $grammar;
	private $fp;
	private $filename;
	private $dir = 'html';
	
	public function __construct( $parser_name, $fsm, $grammar ) {
		$this->name = $parser_name;
		$this->filename = $parser_name . "Visualization";
		$this->grammar = $grammar;
		$this->fsm = $fsm;
		
		
		$this->goto_map = array();
		foreach( $this->fsm->get_statetable() as $state_id => $map ) {
			foreach( $map as $symbol => $action_arr ) {
				list( $action, $target ) = $action_arr;
				if( $action == 'goto' ) {
					if( !isset( $this->goto_map[$symbol] ) )
						$this->goto_map[$symbol] = array();
					$this->goto_map[$symbol][$state_id] = $target;
				}
			}
		}
	}
	
	public function generate() {
		if( !is_dir( $this->dir ) && !mkdir( $this->dir, 0755, true ) )
			throw new GeneratorException( "Could not create dir $class_dir" );
		
		$this->fp = fopen( $this->dir . DIRECTORY_SEPARATOR . $this->filename.'.html', 'w' );
		
		ob_start( array( $this, 'write_block'), 1024 );
		$this->build_html();
		ob_end_flush();
		
		fclose( $this->fp );
	}
	
	public function write_block( $block ) {
		fwrite( $this->fp, $block );
	}
	
	private function build_html() {
?>
<!DOCTYPE html>
<html>
<head>
<title>Visualization of parser <?php echo htmlentities($this->name);?></title>
<style type="text/css">
td, th {
	border-bottom: 1px solid #bbbbbb;
	border-right: 1px solid #bbbbbb;
	margin: 0;
	padding: 2px;
}

.inner_table {
	padding: 0px;
}

.inner_table table {
	width: 100%;
	margin: 0;
	padding: 0;
}

.inner_table td, .inner_table th {
	border: 0;
}

td.bar, th.bar {
	background-color: #dddddd;
}

td.target {
	text-decoration: underline;
}

td.mark {
	background-color: #dddddd;
}

</style>
</head>
<body>
<h1>Visualization of parser <?php echo htmlentities($this->name);?></h1>
<h2>Lexical analysis</h2>
<table>
<?php foreach( $this->grammar->get_tokens() as $token => $match ): ?>
<tr>
<th><?php echo htmlentities( $token ); ?></th>
<td><?php echo htmlentities($match); ?></td>
</tr>
<?php endforeach; ?>
</table>

<h2>LR Parser table</h2>
<table>
<tr>
<th></th>
<th></th>
<th class="bar"></th>
<?php foreach( $this->grammar->terminals() as $sym ): if($sym[0]=='_') continue;?>
<th><?php echo htmlentities($sym); ?></th>
<?php endforeach; ?>
<th class="bar"></th>
<?php foreach( $this->grammar->non_terminals() as $sym ): ?>
<th><?php echo htmlentities($sym); ?></th>
<?php endforeach; ?>
</tr>

<?php foreach( $this->fsm->get_statetable() as $state_id => $map ):?>
<tr>
<th><?php echo htmlentities($state_id); ?></th>
<td class="inner_table">
<table>
<?php foreach( $this->fsm->get_state($state_id)->closure() as $item ):?>
<tr>
<th><?php echo htmlentities($item->get_name());?></th>
<td class="target"><?php echo htmlentities($item->generates());?></td>
<td>=</td>
<?php foreach( $item->get_symbols() as $i=>$sym ): ?>
<td<?php if( $item->get_ptr() == $i ) echo ' class="mark"';?>><?php echo $sym; ?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
</td>
<td class="bar"></td>
<?php foreach( $this->grammar->terminals() as $sym ): if($sym[0]=='_') continue; ?>
<td><?php
if( isset( $map[$sym] ) ) {
	list($action, $target) = $map[$sym];
	print $action.'<br/>'.$target;
}
?></td>
<?php endforeach; ?>
<td class="bar"></td>
<?php foreach( $this->grammar->non_terminals() as $sym ): ?>
<td><?php
if( isset( $map[$sym] ) ) {
	list($action, $target) = $map[$sym];
	print $action.'<br/>'.$target;
}
?></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
<?php
	}
}