export interface SignaturePlan {
    id: number
    container: 'archivo' | 'token' | 'combo'
    validity_years: number
    subscriber_type: string
    pvp: number
  }
  