import { createClient } from '@supabase/supabase-js'

let cachedClient = null

export function getSupabaseClient() {
	if (cachedClient) {
		return cachedClient
	}

	const rawUrl = (import.meta.env.VITE_SUPABASE_URL || '').trim()
	const rawKey = (import.meta.env.VITE_SUPABASE_ANON_KEY || '').trim()
	const supabaseUrl = rawUrl.replace(/\/+$/, '')
	const supabaseAnonKey = rawKey

	if (!supabaseUrl || !supabaseAnonKey) {
		throw new Error(
			'Supabase is not configured. Set VITE_SUPABASE_URL and VITE_SUPABASE_ANON_KEY (build-time) and redeploy.'
		)
	}

	try {
		// eslint-disable-next-line no-new
		new URL(supabaseUrl)
	} catch {
		throw new Error(`Invalid VITE_SUPABASE_URL: ${supabaseUrl}`)
	}

	cachedClient = createClient(supabaseUrl, supabaseAnonKey)
	return cachedClient
}
