# Decision Log

## 2025-02-25 - Savings History Implementation

**Context:** Need to track savings updates over time with parent-child relationships, similar to how payments and income are tracked.

**Decision:** Implement parent-child relationship for savings updates:

1. Database Changes:
   ```sql
   ALTER TABLE `savings` 
   ADD `parent_id` int(11) DEFAULT NULL AFTER `user_id`,
   ADD `update_type` enum('initial','update') DEFAULT 'initial',
   ADD KEY `parent_id` (`parent_id`),
   ADD CONSTRAINT `savings_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `savings` (`id`) ON DELETE CASCADE;
   ```

2. API Changes:
   - Modify `add_saving` endpoint to set update_type as 'initial'
   - Create new `update_saving` endpoint that:
     - Creates a new record with parent_id pointing to original record
     - Sets update_type as 'update'
     - Copies over name, currency, target_amount, target_date
     - Updates current_amount with new value

3. Frontend Changes:
   - Add history view to show all updates for a saving
   - Modify update form to use new API endpoint
   - Add visualization of saving progress over time

**Rationale:**
- Parent-child relationship allows tracking history of updates
- Similar to existing payments/income structure for consistency
- Enables future features like progress visualization

**Implementation Details:**
1. Database:
   - Parent savings record (initial entry)
     - parent_id: NULL
     - update_type: 'initial'
   - Child savings records (updates)
     - parent_id: Points to original record
     - update_type: 'update'
     - Maintains history of amount changes

2. API:
   ```php
   // Example structure for update_saving endpoint
   function updateSaving($data) {
       // Get original saving
       $original = getSavingById($data['id']);
       
       // Create new record with parent reference
       $updateData = [
           'user_id' => $original['user_id'],
           'parent_id' => $original['id'],
           'name' => $original['name'],
           'target_amount' => $original['target_amount'],
           'current_amount' => $data['current_amount'],
           'currency' => $original['currency'],
           'start_date' => $original['start_date'],
           'target_date' => $original['target_date'],
           'update_type' => 'update'
       ];
       
       return insertSaving($updateData);
   }
   ```

3. Frontend:
   ```javascript
   // Example structure for displaying history
   function displaySavingsHistory(savingId) {
       return ajaxRequest({
           action: 'get_savings_history',
           id: savingId
       }).done(function(response) {
           if (response.status === 'success') {
               const updates = response.data.map(update => ({
                   date: update.created_at,
                   amount: update.current_amount,
                   type: update.update_type
               }));
               
               // Display updates in a timeline view
               renderSavingsTimeline(updates);
           }
       });
   }
   ```

**Next Steps:**
1. Update database schema
2. Implement API changes
3. Modify frontend code
4. Add visualization features