<td class="text-center">
                  <button onclick="openDrawer(<?php echo $data['id']; ?>)" type="button" class="btn btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                      <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                      <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                    </svg>
                  </button>
                  <a href="purchase_detail.php" class="link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                      <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                      <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                    </svg>
                  </a>

                  <!-- Edit Drawer For Purchase -->
                  <div class="btn-group">
                    <div class="container">
                    <div id="drawer<?php echo $data['id']; ?>" class="drawer shadow-lg <?php echo ($drawerToOpen == $data['id']) ? 'open' : ''; ?>">
                      <div class="drawer-header d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">Edit Purchase</h5>
                        <button type="button" class="btn-close" onclick="closeDrawer(<?php echo $data['id']; ?>)"></button>
                      </div>

                      <div class="drawer-body p-4">
                        <form action="" method="post">
                          <input type="hidden" name="update_id" value="<?php echo $data['id']; ?>">

                          <div class="row mb-3">
                            <div class="col text-left">
                              <label for="">Date</label>
                              <input type="date" class="form-control" name="date" value="<?php echo $data['date']; ?>">
                              <span style="color:red;"><?php echo empty($dateErrorDrawer) ? '' : '*'.$dateErrorDrawer;?></span>
                            </div>
                            <div class="col text-left">
                              <label for="">GRN_No</label>
                              <input type="text" class="form-control" name="grn_no" value="<?php echo $data['grn_no']; ?>">
                              <span style="color:red;"><?php echo empty($grn_noErrorDrawer) ? '' : '*'.$grn_noErrorDrawer;?></span>
                            </div>
                          </div>

                          <div class="row mb-3">
                            <div class="col text-left">
                              <label class="form-label">Supplier Id</label>
                              <input type="text" id="supplier_id<?php echo $data['id']; ?>" class="form-control" name="supplier_id" value="<?php echo $data['supplier_id']; ?>" 
                                oninput="fetchSupplierNameFromIdDrawer(<?php echo $data['id']; ?>)">
                              <span style="color:red;"><?php echo empty($supplier_idErrorDrawer) ? '' : '*'.$supplier_idErrorDrawer; ?></span>
                            </div>
                            <div class="col text-left">
                              <label class="form-label">Supplier Name</label>
                              <input type="text" id="supplier_name<?php echo $data['id']; ?>" class="form-control" 
                                oninput="fetchSupplierIdFromNameDrawer(<?php echo $data['id']; ?>)">
                            </div>
                          </div>

                          <div class="row">
                            <div class="col text-left">
                              <label for="">PO No</label>
                              <select name="po_no" class="form-control">
                                <option value="">Select PO_No</option>
                                <?php
                                $po_nostmt = $pdo->prepare("SELECT * FROM purchase_order WHERE status LIKE '%ending%' ORDER BY id DESC");
                                $po_nostmt->execute();
                                $po_nodatas = $po_nostmt->fetchAll();
                                foreach ($po_nodatas as $po_nodata) {
                                  $selected = ($po_nodata['order_no'] == $data['po_no']) ? 'selected' : '';
                                  echo "<option value='{$po_nodata['order_no']}' $selected>{$po_nodata['order_no']}</option>";
                                }
                                ?>
                              </select>
                              <span style="color:red;"><?php echo empty($grn_noErrorDrawer) ? '' : '*'.$grn_noErrorDrawer;?></span>
                            </div>
                            <div class="col text-left">
                              <label for="">Payment</label>
                              <select name="type" class="form-control">
                                <option value="cash" <?php echo $data['type']=='cash'?'selected':''; ?>>Cash</option>
                                <option value="credit" <?php echo $data['type']=='credit'?'selected':''; ?>>Credit</option>
                              </select>
                            </div>
                          </div>

                          <div class="d-flex justify-content-end gap-2 pt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="closeDrawer(<?php echo $data['id']; ?>)">Cancel</button>
                            <button type="submit" name="update_btn" class="btn btn-purple text-light ml-2 px-4">Update</button>
                          </div>

                        </form>
                      </div>
                    </div>

                    <div id="drawerBackdrop<?php echo $data['id']; ?>" class="drawer-backdrop <?php echo ($drawerToOpen == $data['id']) ? 'show' : ''; ?>" onclick="closeDrawer(<?php echo $data['id']; ?>)"></div>
                    </div>
                  </div>
                </td>
                <script>
  function openDrawer(id) {
    document.getElementById("drawer" + id).classList.add("open");
    document.getElementById("drawerBackdrop" + id).classList.add("show");
  }

  function closeDrawer(id) {
    document.getElementById("drawer" + id).classList.remove("open");
    document.getElementById("drawerBackdrop" + id).classList.remove("show");
  }
</script>