import { createClient } from '@supabase/supabase-js'

const supabaseUrl = 'https://jqkbksjrwsftsofojxqa.supabase.co'
const supabaseAnonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Impxa2Jrc2pyd3NmdHNvZm9qeHFhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjYwNjY4ODQsImV4cCI6MjA4MTY0Mjg4NH0.BxaqBjNLNFePSs1h5zjVdFxICjofuDIgIll4vyHfZO0'

export const supabase = createClient(supabaseUrl, supabaseAnonKey)
