-- Create the notes table
CREATE TABLE IF NOT EXISTS notes (
    id VARCHAR(32) PRIMARY KEY,
    content TEXT NOT NULL,
    password_hash VARCHAR(255) NULL,
    max_views INT NOT NULL DEFAULT 1,
    current_views INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_destroyed BOOLEAN DEFAULT FALSE
);

-- Create index for better performance
CREATE INDEX idx_notes_created_at ON notes(created_at);
CREATE INDEX idx_notes_is_destroyed ON notes(is_destroyed);
