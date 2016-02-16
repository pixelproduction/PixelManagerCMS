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

class AssigndatatablerowsController extends DataExchangeController
{

    public function getallrowsAction()
    {
        $data_table_class_name = Request::postParam('dataTableClassName');
        $language_id = Request::postParam('languageId');
        if (($data_table_class_name === null) || ($language_id === null)) {
            $this->error(self::RESULT_ERROR_BAD_REQUEST);
            return;
        }
        $result = array();
        if (class_exists($data_table_class_name, true)) {
            $data_table = new $data_table_class_name();
            if ($data_table instanceof DataTableInterface) {
                $rows = $data_table->getAllRowsForAssignmentList($language_id);
                if ($rows !== false) {
                    if (is_array($rows)) {
                        $result[] = $rows;
                    }
                }
            }
        }
        $this->success($result);
    }

}
