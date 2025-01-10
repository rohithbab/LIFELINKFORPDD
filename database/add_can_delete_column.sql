-- Add can_delete column to recipient_notifications table
ALTER TABLE recipient_notifications
ADD COLUMN can_delete BOOLEAN DEFAULT FALSE;
