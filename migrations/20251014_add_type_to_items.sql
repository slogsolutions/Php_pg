ALTER TABLE proposal_items
  ADD COLUMN type VARCHAR(32) NOT NULL DEFAULT 'content' AFTER label;
