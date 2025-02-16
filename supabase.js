// Supabase konfigürasyonu
const SUPABASE_URL = 'https://yynqczhydoeseqvzpvar.supabase.co'
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inl5bnFjemh5ZG9lc2VxdnpwdmFyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Mzk3MTAyODAsImV4cCI6MjA1NTI4NjI4MH0.EB8sGC5-h418TKU02lJ8zju5sx4Oy38_AfmV36oIibA'

// Supabase istemcisini oluştur
const supabase = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY)

// Kullanıcı işlemleri
async function signUp(email, password) {
    try {
        const { user, error } = await supabase.auth.signUp({
            email: email,
            password: password,
        })
        if (error) throw error
        return user
    } catch (error) {
        console.error('Error signing up:', error.message)
        throw error
    }
}

async function signIn(email, password) {
    try {
        const { user, error } = await supabase.auth.signInWithPassword({
            email: email,
            password: password,
        })
        if (error) throw error
        return user
    } catch (error) {
        console.error('Error signing in:', error.message)
        throw error
    }
}

async function signOut() {
    try {
        const { error } = await supabase.auth.signOut()
        if (error) throw error
    } catch (error) {
        console.error('Error signing out:', error.message)
        throw error
    }
}

// Veri işlemleri
async function getPayments() {
    try {
        const { data, error } = await supabase
            .from('payments')
            .select('*')
            .order('created_at', { ascending: false })

        if (error) throw error
        return data
    } catch (error) {
        console.error('Error fetching payments:', error.message)
        throw error
    }
}

async function addPayment(payment) {
    try {
        const { data, error } = await supabase
            .from('payments')
            .insert([payment])
            .select()

        if (error) throw error
        return data[0]
    } catch (error) {
        console.error('Error adding payment:', error.message)
        throw error
    }
}

async function updatePayment(id, payment) {
    try {
        const { data, error } = await supabase
            .from('payments')
            .update(payment)
            .eq('id', id)
            .select()

        if (error) throw error
        return data[0]
    } catch (error) {
        console.error('Error updating payment:', error.message)
        throw error
    }
}

async function deletePayment(id) {
    try {
        const { error } = await supabase
            .from('payments')
            .delete()
            .eq('id', id)

        if (error) throw error
    } catch (error) {
        console.error('Error deleting payment:', error.message)
        throw error
    }
}

// Gelir işlemleri
async function getIncomes() {
    try {
        const { data, error } = await supabase
            .from('incomes')
            .select('*')
            .order('created_at', { ascending: false })

        if (error) throw error
        return data
    } catch (error) {
        console.error('Error fetching incomes:', error.message)
        throw error
    }
}

async function addIncome(income) {
    try {
        const { data, error } = await supabase
            .from('incomes')
            .insert([income])
            .select()

        if (error) throw error
        return data[0]
    } catch (error) {
        console.error('Error adding income:', error.message)
        throw error
    }
}

async function updateIncome(id, income) {
    try {
        const { data, error } = await supabase
            .from('incomes')
            .update(income)
            .eq('id', id)
            .select()

        if (error) throw error
        return data[0]
    } catch (error) {
        console.error('Error updating income:', error.message)
        throw error
    }
}

async function deleteIncome(id) {
    try {
        const { error } = await supabase
            .from('incomes')
            .delete()
            .eq('id', id)

        if (error) throw error
    } catch (error) {
        console.error('Error deleting income:', error.message)
        throw error
    }
}

// Birikim işlemleri
async function getSavings() {
    try {
        const { data, error } = await supabase
            .from('savings')
            .select('*')
            .order('created_at', { ascending: false })

        if (error) throw error
        return data
    } catch (error) {
        console.error('Error fetching savings:', error.message)
        throw error
    }
}

async function addSaving(saving) {
    try {
        const { data, error } = await supabase
            .from('savings')
            .insert([saving])
            .select()

        if (error) throw error
        return data[0]
    } catch (error) {
        console.error('Error adding saving:', error.message)
        throw error
    }
}

async function updateSaving(id, saving) {
    try {
        const { data, error } = await supabase
            .from('savings')
            .update(saving)
            .eq('id', id)
            .select()

        if (error) throw error
        return data[0]
    } catch (error) {
        console.error('Error updating saving:', error.message)
        throw error
    }
}

async function deleteSaving(id) {
    try {
        const { error } = await supabase
            .from('savings')
            .delete()
            .eq('id', id)

        if (error) throw error
    } catch (error) {
        console.error('Error deleting saving:', error.message)
        throw error
    }
}

// Gerçek zamanlı dinleyiciler
function subscribeToPayments(callback) {
    return supabase
        .from('payments')
        .on('*', payload => {
            callback(payload)
        })
        .subscribe()
}

function subscribeToIncomes(callback) {
    return supabase
        .from('incomes')
        .on('*', payload => {
            callback(payload)
        })
        .subscribe()
}

function subscribeToSavings(callback) {
    return supabase
        .from('savings')
        .on('*', payload => {
            callback(payload)
        })
        .subscribe()
}

export {
    supabase,
    signUp,
    signIn,
    signOut,
    // Ödeme işlemleri
    getPayments,
    addPayment,
    updatePayment,
    deletePayment,
    // Gelir işlemleri
    getIncomes,
    addIncome,
    updateIncome,
    deleteIncome,
    // Birikim işlemleri
    getSavings,
    addSaving,
    updateSaving,
    deleteSaving,
    // Gerçek zamanlı dinleyiciler
    subscribeToPayments,
    subscribeToIncomes,
    subscribeToSavings
} 