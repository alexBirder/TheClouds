<form mathod="GET" action="./">
    <input type="hidden" name="module" value="<?=$this->module_id?>">
    <input type="hidden" name="action" value="<?=$this->action?>">
    <table class="contentTable filters">
        <tr class="filter_header">
            <td style="width: 150px;"><b style="font-weight: 500;">Фильтр по рубрикам:</b></td>
            <td>
                <?php echo '<select name="filter" class="areasTable--input" onchange="this.form.submit();">';
                    echo '<option value="0" class="listTitle">-- Показывать публикации со всех рубрик --</option>';
                    $filters = TTree::children_all($this->DB, 'news_issues', 0, null, array('`sort` DESC', '`id` DESC'));
                    foreach($filters as $tmp){
                    printf('<option value="%d" %s>%s%s</option>', $tmp['id'], $tmp['id'] == $filter ? 'selected' : '', str_repeat('&nbsp;', $tmp['level'] * 3), $tmp['title']);
                    }
                    echo '</select>';
                ?>
            </td>
        </tr>
    </table>
</form>