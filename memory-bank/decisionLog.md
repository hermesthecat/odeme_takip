# Decision Log

## 2025-02-25 - Adding Exchange Rate to Savings

**Context:** The income feature has exchange rate functionality that converts amounts to the user's base currency. This functionality would be useful for the savings feature as well, especially for users tracking savings in multiple currencies.

**Decision:** Implement exchange rate functionality for savings:

1. Database Changes:
   ```sql
   ALTER TABLE `savings` 
   ADD `exchange_rate` decimal(10,6) DEFAULT NULL AFTER `currency`;
   ```

2. API Changes:
   - Modify `add_saving` to store exchange rate
   - Update `update_saving` to handle exchange rate
   - Modify savings loading to include converted amounts

3. Frontend Changes:
   - Add exchange rate handling to savings form
   - Display converted amounts in savings list
   - Include exchange rate in history view

**Rationale:**
- Provides consistency with income feature
- Helps users track savings in different currencies
- Makes total savings calculation more accurate
- Enables better financial planning in user's base currency

**Implementation Plan:**
1. Database:
   - Add exchange_rate column to savings table
   - Update existing records with current exchange rates

2. API:
   - Add currency conversion logic to savings endpoints
   - Include exchange rate in savings history
   - Calculate totals in base currency

3. Frontend:
   - Update forms to handle exchange rates
   - Show converted amounts in list view
   - Include rate information in history display

**Next Steps:**
1. Update database schema
2. Implement API changes
3. Modify frontend code
4. Test currency conversion accuracy

## 2025-02-25 - Savings Update Functionality Improvements

**Context:** Users reported issues with the savings update functionality, particularly with the current amount not updating correctly in the list and the edit button not working.

**Decision:** Implement several improvements to the savings update system:

1. API Changes:
   - Added new `get_saving_details` endpoint for fetching detailed saving information
   - Modified `update_saving` to set the original record's current_amount to 0
   - Added `get_savings_history` endpoint for fetching the history of a saving

2. Frontend Changes:
   - Modified the edit button to use saving ID instead of passing the full saving object
   - Improved history display with formatted dates and clearer update types
   - Added proper error handling for failed API requests

**Rationale:**
- Passing saving ID instead of full object prevents JSON serialization issues
- Setting original record's current_amount to 0 prevents confusion in the list view
- Formatting dates and update types improves readability of the history

**Implementation:**
- Added new API endpoint for fetching saving details
- Updated savings.js to use the new endpoints
- Improved error handling and user feedback
- Enhanced history display formatting

**Next Steps:**
1. Monitor the improved functionality for any issues
2. Consider adding more detailed information to the history display
3. Look for opportunities to improve the user interface

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