<?php

/**
 * PixelManager CMS (Community Edition)
 * Copyright (C) 2016 PixelProduction (http://www.pixelproduction.de)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class DataTableExchangeController extends DataExchangeController
{

    protected $data_table = null;

    public function setDataTable(DataTableInterface $data_table)
    {
        $this->data_table = $data_table;
    }

    public function getDataTable()
    {
        return ($this->data_table);
    }

    public function defaultAction()
    {
        $this->getallrowsAction();
    }

    public function getallrowsAction()
    {
        $data['rows'] = $this->getDataTable()->getAllRows(false);
        $this->success($data);
    }

    public function getstructureAction()
    {
        $data_table = $this->getDataTable();
        $data = array(
            'fields' => $data_table->getFields(),
            'columns' => $data_table->getColumns(),
            'languagesConfig' => $data_table->getLanguagesConfig(),
            'editorTabs' => $data_table->getEditorTabs(),
            'options' => array(
                'queryBeforeCreate' => $data_table->getQueryBeforeCreate(),
                'queryBeforeUpdate' => $data_table->getQueryBeforeUpdate(),
                'queryBeforeDelete' => $data_table->getQueryBeforeDelete(),
            ),
        );
        $this->success($data);
    }

    public function getrowAction()
    {
        $id = Request::postParam('id');
        if ($id === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $data['row'] = $this->getDataTable()->getRow($id, true);
        $data['id'] = $id;
        $this->success($data);
    }

    public function queryupdaterowAction()
    {
        $id = Request::postParam('id');
        $json_row_data = Request::postParam('row');

        if ($id === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        if ($json_row_data === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $row_data = json_decode($json_row_data, true);

        $data_table = $this->getDataTable();
        $query_ok = $data_table->queryUpdateRow($id, $row_data);
        $query_error_message = '';
        if (!$query_ok) {
            $query_error_message = $data_table->getLastQueryErrorMessage();
        }
        $this->success(
            array(
                'queryOk' => $query_ok,
                'queryErrorMessage' => $query_error_message,
            )
        );
    }

    public function updaterowAction()
    {
        $id = Request::postParam('id');
        $json_row_data = Request::postParam('row');

        if ($id === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        if ($json_row_data === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $row_data = json_decode($json_row_data, true);

        $res = $this->getDataTable()->updateRow($id, $row_data);
        if ($res === false) {
            $this->error(self::RESULT_ERROR_UNKOWN);
            return;
        }

        $crud_message_array = $this->getDataTable()->getCrudMessageArray();
        $this->success(
            array(
                'rowId' => intval($id),
                'message' => $crud_message_array['message'],
                'messageType' => $crud_message_array['type'],
            )
        );
    }

    public function getstandardvaluesAction()
    {
        $this->success(array());
    }

    public function querycreaterowAction()
    {
        $json_row_data = Request::postParam('row');

        if ($json_row_data === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $row_data = json_decode($json_row_data, true);

        $data_table = $this->getDataTable();
        $query_ok = $data_table->queryCreateRow($row_data);
        $query_error_message = '';
        if (!$query_ok) {
            $query_error_message = $data_table->getLastQueryErrorMessage();
        }
        $this->success(
            array(
                'queryOk' => $query_ok,
                'queryErrorMessage' => $query_error_message,
            )
        );
    }

    public function createrowAction()
    {
        $json_row_data = Request::postParam('row');

        if ($json_row_data === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $row_data = json_decode($json_row_data, true);

        $res = $this->getDataTable()->createRow($row_data);
        if ($res === false) {
            $this->error(self::RESULT_ERROR_UNKOWN);
            return;
        }

        $crud_message_array = $this->getDataTable()->getCrudMessageArray();
        $this->success(
            array(
                'lastInsertId' => intval($res),
                'rowId' => intval($res),
                'message' => $crud_message_array['message'],
                'messageType' => $crud_message_array['type'],
            )
        );
    }

    public function querydeleterowsAction()
    {
        $json_id_list = Request::postParam('idList');

        if ($json_id_list === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $query_ok = true;
        $data_table = $this->getDataTable();
        $id_list = json_decode($json_id_list, true);

        if (is_array($id_list)) {
            if (count($id_list) > 0) {
                foreach ($id_list as $id) {
                    $res = $data_table->queryDeleteRow($id);
                    if ($res === false) {
                        $query_ok = false;
                        break;
                    }
                }
            }
        }

        $query_error_message = '';
        if (!$query_ok) {
            $query_error_message = $data_table->getLastQueryErrorMessage();
        }
        $this->success(
            array(
                'queryOk' => $query_ok,
                'queryErrorMessage' => $query_error_message,
            )
        );
    }

    public function deleterowsAction()
    {
        $json_id_list = Request::postParam('idList');

        if ($json_id_list === null) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }

        $id_list = json_decode($json_id_list, true);

        if (is_array($id_list)) {
            if (count($id_list) > 0) {
                foreach ($id_list as $id) {
                    $res = $this->getDataTable()->deleteRow($id);
                    if ($res === false) {
                        $this->error(self::RESULT_ERROR_UNKOWN);
                        return;
                    }
                }
            }
        }

        $crud_message_array = $this->getDataTable()->getCrudMessageArray();
        $this->success(
            array(
                'message' => $crud_message_array['message'],
                'messageType' => $crud_message_array['type'],
            )
        );
    }

}
