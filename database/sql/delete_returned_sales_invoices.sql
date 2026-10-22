-- =====================================================================
-- SQL SCRIPT: CLEAN DELETION OF NON-DG KHAN RETURNED SALES INVOICES
-- Restores unreturned stocks, cleans all vouchers, journal entries,
-- customer ledgers, returns, and sales records.
-- PRESERVES DG KHAN SALES & RETURNS (Zone 3) 100% INTACT.
-- =====================================================================

START TRANSACTION;

-- 1. Restore Stock ONLY for Unreturned Remaining Quantities
-- (If sold 10, returned 10 -> remaining 0, stock unchanged)
-- (If sold 10, returned 5 -> remaining 5, restores 5 pieces to warehouse)
UPDATE warehouse_stocks ws
JOIN (
    SELECT 
        si.warehouse_id,
        si.product_id,
        GREATEST(0, SUM(si.total_pieces) - COALESCE(ret.total_returned, 0)) AS qty_to_restore
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
    LEFT JOIN (
        SELECT sri.product_id, sr.sale_id, SUM(sri.qty) AS total_returned
        FROM sale_return_items sri
        JOIN sale_returns sr ON sr.id = sri.sale_return_id
        WHERE sr.sale_id IN (1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120)
        GROUP BY sri.product_id, sr.sale_id
    ) ret ON ret.product_id = si.product_id AND ret.sale_id = si.sale_id
    WHERE si.sale_id IN (1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120)
      AND (s.sale_status = 'posted' OR s.sale_status = 'returned')
    GROUP BY si.warehouse_id, si.product_id
) diff ON ws.warehouse_id = diff.warehouse_id AND ws.product_id = diff.product_id
JOIN products p ON p.id = ws.product_id
SET ws.total_pieces = ws.total_pieces + diff.qty_to_restore,
    ws.quantity = (ws.total_pieces + diff.qty_to_restore) / IF(p.pieces_per_box > 0, p.pieces_per_box, 1)
WHERE diff.qty_to_restore > 0;

-- 2. Delete Stock Movements for Sales and Returns
DELETE FROM stock_movements 
WHERE (ref_type = 'sale' AND ref_id IN (1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120))
   OR (ref_type = 'SALE_RETURN' AND ref_id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23));

-- 3. Delete Journal Entries linked to Vouchers
DELETE je FROM journal_entries je
JOIN voucher_masters vm ON je.source_id = vm.id AND je.source_type = 'App\\Models\\VoucherMaster'
WHERE vm.remarks LIKE '%#INV-0001%' OR vm.remarks LIKE '%#INV-0003%' OR vm.remarks LIKE '%#INV-0020%'
   OR vm.remarks LIKE '%#INV-0021%' OR vm.remarks LIKE '%#INV-0029%' OR vm.remarks LIKE '%#INV-0036%'
   OR vm.remarks LIKE '%#INV-0044%' OR vm.remarks LIKE '%#INV-0049%' OR vm.remarks LIKE '%#INV-0095%'
   OR vm.remarks LIKE '%#INV-0096%' OR vm.remarks LIKE '%#INV-0097%' OR vm.remarks LIKE '%#INV-0100%'
   OR vm.remarks LIKE '%#INV-0109%' OR vm.remarks LIKE '%#INV-0111%' OR vm.remarks LIKE '%#INV-0113%'
   OR vm.remarks LIKE '%#INV-0115%' OR vm.remarks LIKE '%#INV-0118%'
   OR vm.remarks LIKE '%SR-0001%' OR vm.remarks LIKE '%SR-0002%' OR vm.remarks LIKE '%SR-0004%'
   OR vm.remarks LIKE '%SR-0005%' OR vm.remarks LIKE '%SR-0006%' OR vm.remarks LIKE '%SR-0009%'
   OR vm.remarks LIKE '%SR-0010%' OR vm.remarks LIKE '%SR-0011%' OR vm.remarks LIKE '%SR-0013%'
   OR vm.remarks LIKE '%SR-0014%' OR vm.remarks LIKE '%SR-0015%' OR vm.remarks LIKE '%SR-0017%'
   OR vm.remarks LIKE '%SR-0018%' OR vm.remarks LIKE '%SR-0019%' OR vm.remarks LIKE '%SR-0020%'
   OR vm.remarks LIKE '%SR-0021%' OR vm.remarks LIKE '%SR-0022%' OR vm.remarks LIKE '%SR-0023%';

-- 4. Delete Direct Journal Entries for Sales
DELETE FROM journal_entries 
WHERE source_type = 'App\\Models\\Sale' 
  AND source_id IN (1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120);

-- 5. Delete Voucher Details
DELETE vd FROM voucher_details vd
JOIN voucher_masters vm ON vd.voucher_master_id = vm.id
WHERE vm.remarks LIKE '%#INV-0001%' OR vm.remarks LIKE '%#INV-0003%' OR vm.remarks LIKE '%#INV-0020%'
   OR vm.remarks LIKE '%#INV-0021%' OR vm.remarks LIKE '%#INV-0029%' OR vm.remarks LIKE '%#INV-0036%'
   OR vm.remarks LIKE '%#INV-0044%' OR vm.remarks LIKE '%#INV-0049%' OR vm.remarks LIKE '%#INV-0095%'
   OR vm.remarks LIKE '%#INV-0096%' OR vm.remarks LIKE '%#INV-0097%' OR vm.remarks LIKE '%#INV-0100%'
   OR vm.remarks LIKE '%#INV-0109%' OR vm.remarks LIKE '%#INV-0111%' OR vm.remarks LIKE '%#INV-0113%'
   OR vm.remarks LIKE '%#INV-0115%' OR vm.remarks LIKE '%#INV-0118%'
   OR vm.remarks LIKE '%SR-0001%' OR vm.remarks LIKE '%SR-0002%' OR vm.remarks LIKE '%SR-0004%'
   OR vm.remarks LIKE '%SR-0005%' OR vm.remarks LIKE '%SR-0006%' OR vm.remarks LIKE '%SR-0009%'
   OR vm.remarks LIKE '%SR-0010%' OR vm.remarks LIKE '%SR-0011%' OR vm.remarks LIKE '%SR-0013%'
   OR vm.remarks LIKE '%SR-0014%' OR vm.remarks LIKE '%SR-0015%' OR vm.remarks LIKE '%SR-0017%'
   OR vm.remarks LIKE '%SR-0018%' OR vm.remarks LIKE '%SR-0019%' OR vm.remarks LIKE '%SR-0020%'
   OR vm.remarks LIKE '%SR-0021%' OR vm.remarks LIKE '%SR-0022%' OR vm.remarks LIKE '%SR-0023%';

-- 6. Delete Voucher Masters
DELETE FROM voucher_masters
WHERE remarks LIKE '%#INV-0001%' OR remarks LIKE '%#INV-0003%' OR remarks LIKE '%#INV-0020%'
   OR remarks LIKE '%#INV-0021%' OR remarks LIKE '%#INV-0029%' OR remarks LIKE '%#INV-0036%'
   OR remarks LIKE '%#INV-0044%' OR remarks LIKE '%#INV-0049%' OR remarks LIKE '%#INV-0095%'
   OR remarks LIKE '%#INV-0096%' OR remarks LIKE '%#INV-0097%' OR remarks LIKE '%#INV-0100%'
   OR remarks LIKE '%#INV-0109%' OR remarks LIKE '%#INV-0111%' OR remarks LIKE '%#INV-0113%'
   OR remarks LIKE '%#INV-0115%' OR remarks LIKE '%#INV-0118%'
   OR remarks LIKE '%SR-0001%' OR remarks LIKE '%SR-0002%' OR remarks LIKE '%SR-0004%'
   OR remarks LIKE '%SR-0005%' OR remarks LIKE '%SR-0006%' OR remarks LIKE '%SR-0009%'
   OR remarks LIKE '%SR-0010%' OR remarks LIKE '%SR-0011%' OR remarks LIKE '%SR-0013%'
   OR remarks LIKE '%SR-0014%' OR remarks LIKE '%SR-0015%' OR remarks LIKE '%SR-0017%'
   OR remarks LIKE '%SR-0018%' OR remarks LIKE '%SR-0019%' OR remarks LIKE '%SR-0020%'
   OR remarks LIKE '%SR-0021%' OR remarks LIKE '%SR-0022%' OR remarks LIKE '%SR-0023%';

-- 7. Delete Customer Ledgers
DELETE FROM customer_ledgers
WHERE description LIKE '%INV-0001%' OR description LIKE '%INV-0003%' OR description LIKE '%INV-0020%'
   OR description LIKE '%INV-0021%' OR description LIKE '%INV-0029%' OR description LIKE '%INV-0036%'
   OR description LIKE '%INV-0044%' OR description LIKE '%INV-0049%' OR description LIKE '%INV-0095%'
   OR description LIKE '%INV-0096%' OR description LIKE '%INV-0097%' OR description LIKE '%INV-0100%'
   OR description LIKE '%INV-0109%' OR description LIKE '%INV-0111%' OR description LIKE '%INV-0113%'
   OR description LIKE '%INV-0115%' OR description LIKE '%INV-0118%'
   OR description LIKE '%SR-0001%' OR description LIKE '%SR-0002%' OR description LIKE '%SR-0004%'
   OR description LIKE '%SR-0005%' OR description LIKE '%SR-0006%' OR description LIKE '%SR-0009%'
   OR description LIKE '%SR-0010%' OR description LIKE '%SR-0011%' OR description LIKE '%SR-0013%'
   OR description LIKE '%SR-0014%' OR description LIKE '%SR-0015%' OR description LIKE '%SR-0017%'
   OR description LIKE '%SR-0018%' OR description LIKE '%SR-0019%' OR description LIKE '%SR-0020%'
   OR description LIKE '%SR-0021%' OR description LIKE '%SR-0022%' OR description LIKE '%SR-0023%';

-- 8. Delete Sale Return Items and Sale Returns
DELETE FROM sale_return_items WHERE sale_return_id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23);
DELETE FROM sale_returns WHERE id IN (1, 2, 4, 5, 6, 9, 10, 11, 13, 14, 15, 17, 18, 19, 20, 21, 22, 23);

-- 9. Delete Sale Items and Sales
DELETE FROM sale_items WHERE sale_id IN (1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120);
DELETE FROM sales WHERE id IN (1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120);

COMMIT;
