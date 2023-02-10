<?php

class TableUtil
{

	public static function render($list, $options=array(), $mode='html', $fnFilter=null) {

		$default_columns = "";
		$data_type = "";
		if(isset($options['data_type'])) {
			$data_type = $options['data_type'];
		}
		if(count($list)) {
			$default_columns = array();
			
			$columns = $list[0]::table()->columns;
			if(isset($options['custom_columns']) && $options['custom_columns']) {
				$aux_columns = array();
				foreach (array_keys($list[0]->attributes()) as $key) {
					if(in_array($key, array_keys($columns))) {
						$aux_columns[$key] = $columns[$key];
					} else {
						$aux_columns[$key] = array();
					}
				}
				$columns = $aux_columns;
			}

			foreach ($columns as $key => $column) {
				$default_columns[$key] = array(
					"pk" => $column->pk,
					"name" => $column->name,
					"label" => ucwords(str_replace('_', ' ', $column->name)),
					"hidden" => isset($options["hiddden_columns"]) ? $options["hiddden_columns"]:false,
					"raw_type" => $column->raw_type
				);
			}
			$data_type = get_class($list[0]);
		}

		$custom_columns = isset($options['custom_columns']) ? $options['custom_columns'] : false;

		$default_options = array(
			"box_style" => "",
			"table_id" => "table_main",
			"selectable_rows" => false,
			"title" => "",
			"table_filter" => array(
				'buttons' => array(
                    "download" => array(
                        "hidden" => true
					),
					"find" => array(
                        "hidden" => true
					),
					"clean" => array(
						"hidden" => true
					),
					"metricas" => array(
						"hidden" => true
					)
                ),
			),
			"data_type" => $data_type,
			"columns" => $default_columns,
			"top_buttons" => array(
				
			),
			"buttons" => array(
				"edit" => array(
					"label" => "Editar",
					"style" => "info",
					"size" => "md",
					"onclick" => "editRecord(this);",
					"icon" => "edit",
					"hidden" => false
				),
				"clone" => array(
					"label" => "Clonar",
					"style" => "warning",
					"size" => "md",
					"onclick" => "cloneRecord(this);",
					"icon" => "clone",
					"hidden" => true
				),
				"delete" => array(
					"label" => "Borrar",
					"style" => "danger",
					"size" => "md",
					"onclick" => "deleteRecord(this);",
					"icon" => "remove",
					"confirm" => "¿Deseas borrar el registro?",
					"hidden" => false
				)
			),
			"edit_page" => strtolower($data_type),
			"list_page" => strtolower($data_type) . "s_listado",
			"rows" => array(
				"color_config" => array()
			),
			'update' => null,
			"can_add" => true,
			"ordering" => "asc",
			"ordering_col" => 0,
			"paging" => true,
			"searching" => true,
			"page_length" => 50,
			"info" => true,
			"language" => array(
				"zeroRecords" => ""
			),
			"row_reorder" => false
		);
		extract(array_replace_recursive($default_options, $options));

		if($custom_columns) {
			//seteo columnas
			foreach ($columns as $key => $column) {
				if(isset($default_columns[$key])) {
					$columns[$key] = array_replace_recursive($default_columns[$key], $column);
				}
			}
		}

		$twig = TwigUtil::getInstance();
		$twig->addExtension(new Twig_Extensions_Extension_Text());
		foreach (FiltersUtil::all() as $key => $filter) {
			$twig->addFilter($filter);
		}
		
		//seteo table_id en nombre de botones
		$buttons["edit"]["onclick"] = "editRecord_{$table_id}(event, this);";
		$buttons["clone"]["onclick"] = "cloneRecord_{$table_id}(this);";
		$buttons["delete"]["onclick"] = "deleteRecord_{$table_id}(this);";

		//le pongo el table id papra referenciar a la tabla dentro del filter
		$table_filter['table_config'] = array(
			"table_id" => $table_id,
			"update" => $update
		);
		if(isset($table_filter['fields'])) {
			foreach ($table_filter['fields'] as $key => $field_config) {
				$table_filter['fields'][$key] = FormUtil::getFieldConfig($key, null, null, null, null, $field_config);
			}
		}

		$html = $twig->render('table.html', 
					array(
						'box_style' => $box_style,
						'base_url' => BASE_URL,
						'table_id' => $table_id,
						'table_filter' => $table_filter,
						'title' => $title,
						'columns' => $columns,
						'data_type' => $data_type,
						'data' => ($fnFilter) ? $fnFilter($list) : $list,
						'top_buttons' => $top_buttons,
						'buttons' => $buttons,
						"edit_page" => $edit_page,
						"list_page" => $list_page,
						"can_add" => $can_add,
						"ordering" => $ordering,
						"ordering_col" => (is_array($selectable_rows) || $selectable_rows == true) ? $ordering_col + 1 : $ordering_col,
						"paging" => $paging,
						"searching" => $searching,
						"page_length" => $page_length,
						"info" => $info,
						"language" => $language,
						"row_reorder" => $row_reorder,
						"update" => $update,
						"rows" => $rows,
						'is_group' => LoginUtil::isAGroup(),
						"mode" => $mode,
						'selectable_rows' => $selectable_rows
						)
					);
		if($mode == 'html') {
			return $html;
		} else {
			$html = preg_replace('/\n+/', ' ', $html);
			$html = preg_replace('/ +/', ' ', $html);
			$html = preg_replace('/\t+/', '    ', $html);
			return $html;
		}
	}
}