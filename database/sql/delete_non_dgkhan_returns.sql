-- =====================================================================
-- SQL SCRIPT: CLEAN DELETION OF NON-DG KHAN SALE RETURNS
-- Safely reverses stocks, deletes vouchers & journal entries,
-- cleans customer ledgers, and restores sales status.
-- Preserves DG Khan Returns: SR-0003, SR-0007, SR-0008, SR-0012, SR-0016
-- =====================================================================

START TRANSACTION;

-- 1. Revert Warehouse Stocks (decrement returned pieces)
UPDATE warehouse_stocks ws
JOIN sale_return_items sri ON ws.warehouse_id = sri.warehouse_id AND ws.product_id = sri.product_id
JOIN products p ON p.id = ws.product_id
SET ws.total_pieces = ws.total_pieces - sri.qty,
    ws.quantity = (ws.total_pieces - sri.qty) / IF(p.pieces_per_box > 0, p.pieces_per_box, 1)
WHERE sri.sale_return_id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23);

-- 2. Delete Stock Movements
DELETE FROM stock_movements 
WHERE ref_type = 'SALE_RETURN' 
  AND ref_id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23);

-- 3. Delete Journal Entries linked to vouchers for these returns
DELETE je FROM journal_entries je
JOIN voucher_masters vm ON je.source_id = vm.id AND je.source_type = 'App\\Models\\VoucherMaster'
WHERE vm.remarks LIKE '%SR-0001%'
   OR vm.remarks LIKE '%SR-0002%'
   OR vm.remarks LIKE '%SR-0004%'
   OR vm.remarks LIKE '%SR-0005%'
   OR vm.remarks LIKE '%SR-0006%'
   OR vm.remarks LIKE '%SR-0009%'
   OR vm.remarks LIKE '%SR-0010%'
   OR vm.remarks LIKE '%SR-0011%'
   OR vm.remarks LIKE '%SR-0013%'
   OR vm.remarks LIKE '%SR-0014%'
   OR vm.remarks LIKE '%SR-0015%'
   OR vm.remarks LIKE '%SR-0017%'
   OR vm.remarks LIKE '%SR-0018%'
   OR vm.remarks LIKE '%SR-0019%'
   OR vm.remarks LIKE '%SR-0020%'
   OR vm.remarks LIKE '%SR-0021%'
   OR vm.remarks LIKE '%SR-0022%'
   OR vm.remarks LIKE '%SR-0023%';

-- 4. Delete Voucher Details
DELETE vd FROM voucher_details vd
JOIN voucher_masters vm ON vd.voucher_master_id = vm.id
WHERE vm.remarks LIKE '%SR-0001%'
   OR vm.remarks LIKE '%SR-0002%'
   OR vm.remarks LIKE '%SR-0004%'
   OR vm.remarks LIKE '%SR-0005%'
   OR vm.remarks LIKE '%SR-0006%'
   OR vm.remarks LIKE '%SR-0009%'
   OR vm.remarks LIKE '%SR-0010%'
   OR vm.remarks LIKE '%SR-0011%'
   OR vm.remarks LIKE '%SR-0013%'
   OR vm.remarks LIKE '%SR-0014%'
   OR vm.remarks LIKE '%SR-0015%'
   OR vm.remarks LIKE '%SR-0017%'
   OR vm.remarks LIKE '%SR-0018%'
   OR vm.remarks LIKE '%SR-0019%'
   OR vm.remarks LIKE '%SR-0020%'
   OR vm.remarks LIKE '%SR-0021%'
   OR vm.remarks LIKE '%SR-0022%'
   OR vm.remarks LIKE '%SR-0023%';

-- 5. Delete Voucher Masters
DELETE FROM voucher_masters
WHERE remarks LIKE '%SR-0001%'
   OR remarks LIKE '%SR-0002%'
   OR remarks LIKE '%SR-0004%'
   OR remarks LIKE '%SR-0005%'
   OR remarks LIKE '%SR-0006%'
   OR remarks LIKE '%SR-0009%'
   OR remarks LIKE '%SR-0010%'
   OR remarks LIKE '%SR-0011%'
   OR remarks LIKE '%SR-0013%'
   OR remarks LIKE '%SR-0014%'
   OR remarks LIKE '%SR-0015%'
   OR remarks LIKE '%SR-0017%'
   OR remarks LIKE '%SR-0018%'
   OR remarks LIKE '%SR-0019%'
   OR remarks LIKE '%SR-0020%'
   OR remarks LIKE '%SR-0021%'
   OR remarks LIKE '%SR-0022%'
   OR remarks LIKE '%SR-0023%';

-- 6. Delete Customer Ledger entries
DELETE FROM customer_ledgers
WHERE description LIKE '%SR-0001%'
   OR description LIKE '%SR-0002%'
   OR description LIKE '%SR-0004%'
   OR description LIKE '%SR-0005%'
   OR description LIKE '%SR-0006%'
   OR description LIKE '%SR-0009%'
   OR description LIKE '%SR-0010%'
   OR description LIKE '%SR-0011%'
   OR description LIKE '%SR-0013%'
   OR description LIKE '%SR-0014%'
   OR description LIKE '%SR-0015%'
   OR description LIKE '%SR-0017%'
   OR description LIKE '%SR-0018%'
   OR description LIKE '%SR-0019%'
   OR description LIKE '%SR-0020%'
   OR description LIKE '%SR-0021%'
   OR description LIKE '%SR-0022%'
   OR description LIKE '%SR-0023%';

-- 7. Restore Sales Status to 'posted' for invoices that no longer have any returns
UPDATE sales s
SET s.sale_status = 'posted'
WHERE s.id IN (1, 111, 115, 120)
  AND s.sale_status = 'returned';

-- 8. Delete Sale Return Items
DELETE FROM sale_return_items
WHERE sale_return_id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23);

-- 9. Delete Sale Returns
DELETE FROM sale_returns
WHERE id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23);

COMMIT;
