export interface IValidationRule {
  validate(value: unknown): boolean
  message: string
}

export class RequiredRule implements IValidationRule {
  message = 'Este campo é obrigatório'

  validate(value: unknown): boolean {
    if (value === null || value === undefined) return false
    if (typeof value === 'string') return value.trim().length > 0
    if (Array.isArray(value)) return value.length > 0
    return true
  }
}

export class EmailRule implements IValidationRule {
  message = 'Formato de e-mail inválido'

  validate(value: unknown): boolean {
    if (typeof value !== 'string') return false
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(value)
  }
}

export class MinLengthRule implements IValidationRule {
  message: string

  constructor(private minLength: number) {
    this.message = `Deve ter pelo menos ${minLength} caracteres`
  }

  validate(value: unknown): boolean {
    if (typeof value !== 'string') return false
    return value.length >= this.minLength
  }
}

export class MaxLengthRule implements IValidationRule {
  message: string

  constructor(private maxLength: number) {
    this.message = `Deve ter no máximo ${maxLength} caracteres`
  }

  validate(value: unknown): boolean {
    if (typeof value !== 'string') return false
    return value.length <= this.maxLength
  }
}

export class NumberRule implements IValidationRule {
  message = 'Deve ser um número válido'

  validate(value: unknown): boolean {
    if (value === null || value === undefined || value === '') return false
    return !isNaN(Number(value))
  }
}

export class PositiveNumberRule implements IValidationRule {
  message = 'Deve ser um número positivo'

  validate(value: unknown): boolean {
    if (!new NumberRule().validate(value)) return false
    return Number(value) > 0
  }
}

export class DateRule implements IValidationRule {
  message = 'Data inválida'

  validate(value: unknown): boolean {
    if (!value) return false
    const date = new Date(value as string | number)
    return !isNaN(date.getTime())
  }
}

export class CustomRule implements IValidationRule {
  constructor(
    private validator: (value: unknown) => boolean,
    public message: string,
  ) {}

  validate(value: unknown): boolean {
    return this.validator(value)
  }
}

export class FieldValidator {
  private rules: IValidationRule[] = []

  addRule(rule: IValidationRule): FieldValidator {
    this.rules.push(rule)
    return this
  }

  required(): FieldValidator {
    return this.addRule(new RequiredRule())
  }

  email(): FieldValidator {
    return this.addRule(new EmailRule())
  }

  minLength(length: number): FieldValidator {
    return this.addRule(new MinLengthRule(length))
  }

  maxLength(length: number): FieldValidator {
    return this.addRule(new MaxLengthRule(length))
  }

  number(): FieldValidator {
    return this.addRule(new NumberRule())
  }

  positiveNumber(): FieldValidator {
    return this.addRule(new PositiveNumberRule())
  }

  date(): FieldValidator {
    return this.addRule(new DateRule())
  }

  custom(validator: (value: unknown) => boolean, message: string): FieldValidator {
    return this.addRule(new CustomRule(validator, message))
  }

  validate(value: unknown): { isValid: boolean; errors: string[] } {
    const errors: string[] = []

    for (const rule of this.rules) {
      if (!rule.validate(value)) {
        errors.push(rule.message)
      }
    }

    return {
      isValid: errors.length === 0,
      errors,
    }
  }
}

export class ValidatorFactory {
  static seller() {
    return {
      name: new FieldValidator().required().minLength(2).maxLength(100),

      email: new FieldValidator().required().email().maxLength(255),
    }
  }

  static sale() {
    return {
      seller_id: new FieldValidator().required().number().positiveNumber(),

      amount: new FieldValidator()
        .required()
        .number()
        .positiveNumber()
        .custom((value) => Number(value) >= 0.01, 'Valor deve ser maior que R$ 0,01'),

      sale_date: new FieldValidator()
        .required()
        .date()
        .custom((value) => new Date(value as string) <= new Date(), 'Data não pode ser futura'),
    }
  }

  static login() {
    return {
      email: new FieldValidator().required().email(),

      password: new FieldValidator().required().minLength(6),
    }
  }
}
