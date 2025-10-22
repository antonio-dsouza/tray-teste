export const COMMISSION_RATES = {
  DEFAULT: 0.085,
}

export function calculateCommission(amount: number): number {
  return amount * COMMISSION_RATES.DEFAULT
}
