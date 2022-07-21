<?php 
  // fighter id
  $fighter_id = $_GET['ID'];
  // get fighter from database
  $fighter = new UFC_Datatable( $fighter_id );
?>
<?php  if(!$fighter->id): ?>
  <div style='display:flex;align-items:center;justify-content:center'>Nothing to Display.</div>
<?php else: ?>
  <?php $file_array = (array) $fighter->fighter_json_data; ?>
  <div style="font-weight: 700; font-size: 2rem;">
    <?= $fighter->fighter_name ?> (<?= $fighter->fighter_id ?>)
  </div>
  <!-- Rendering string values -->
  <table id="pricing" class="display" style="width:100%;border: 1px solid rgb(190, 190, 190);">
    <tbody>
      <?php foreach(chunck_array($file_array ) as $string_column): ?>
        <tr style="border: 1px solid rgb(190, 190, 190);">
          <?php foreach ($string_column as $h => $v): ?>
            <th> 
              <div style='background: rgb(229, 229, 229); height: 40px; display:flex; justify-content: center; align-items: center;'>
                <?= $h ?> 
              </div>
            </th>
          <?php endforeach; ?>
        </tr>
        <tr>
          <?php foreach ($string_column as $h => $v): ?>
            <td align="center"> 
              <div style='height: 60px; display: flex; justify-content: center; align-items: center; margin-bottom: 10px;'>
                <?= number_format((float)$v, 2, '.', '') ?> 
              </div>
            </td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Rendering object values -->
  <?php foreach ($file_array as $key => $value): ?>
    <?php if (is_array($value)): ?>
      <?php $title = $key; ?>
      <table id="pricing" class="display" style="width:100%; border: 1px solid rgb(190, 190, 190);">
        <tr>
          <td> 
            <div style='font-weight: 700; font-size: 1.1rem; width: 300px'>
              <?= str_replace(['Opp', 'Sig'], ['Opponent', 'Significant Strikes'], $title) ?>
            </div>
          </td>
        </tr>
        <?php foreach ($value as $subtitle => $str): ?>
          <?php if (is_array($str)): ?>
            <tr>
              <td>  
                <div style='margin-left: 30px; font-weight: 600; font-size: 1rem;'>
                  <?= $subtitle ?> 
                </div>
              </td>
            </tr>
            <div style="margin-left: 60px;">
              <tr>
                <?php if (is_array($str)): ?>
                  <?php $row_display =  array_chunk($str, 4, true); ?>
                  <?php foreach ($row_display as $key => $values): ?>
                    <?php if (is_array($values)): ?>
                      <tr style="border: 1px solid rgb(190, 190, 190);">
                        <?php foreach ($values as $h => $v): ?>
                          <th> 
                            <div style='background: rgb(229, 229, 229); height: 30px; display:flex; justify-content: center; align-items: center;'>
                              <?= $h ?> 
                            </div>
                          </th>
                        <?php endforeach; ?>
                      </tr>
                      <tr>
                        <?php foreach ($values as $h => $v): ?>
                          <td align="center"> 
                            <div style='height: 40px; display: flex; justify-content: center; align-items: center; margin-bottom: 10px;'>
                              <?= number_format((float)$v, 2, '.', '') ?> 
                            </div>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tr>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>