<?php
/**
 * views/edit-column-collapsed.php
 * Show icons for what to do with custom fields
 */
?>

<div data-dkocoledit-post-id="<?php echo $data['post_id']; ?>" class="<?php echo DKOColEdit::slug; ?>-wrapper" id="<?php echo DKOColEdit::slug; ?>-wrapper-post-<?php echo $data['post_id']; ?>">
  <input type="hidden" class="<?php echo DKOColEdit::slug; ?>-select2" id="<?php echo DKOColEdit::slug; ?>-select2-post-<?php echo $data['post_id']; ?>">
  <a class="DKOColEdit-add-key"><span class="icon icon-add">Add new key</span></a>

  <section class="DKOColEdit-add-key-section hidden">
    <h6>Add a new custom field</h6>
    <p><label>k:</label> <input type="text" value="" class="DKOColEdit-add-key--key-input"></p>
    <p><label>v:</label> <input type="text" value="" class="DKOColEdit-add-key--value-input"></p>
    <a class="DKOColEdit-add-key-cancel"><span class="icon icon-delete"></span> Cancel</a>
    <a class="DKOColEdit-add-key-save"><span class="icon icon-save"></span> Save</a>
  </section>

  <section class="DKOColEdit-add-value-section hidden">
    <h6>Edit values for field</h6>
  </section>
</div>
