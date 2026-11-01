export type Member = { id: string; name: string; income: number };
export type CommonExpense = { id: string; name: string; amount: number };
export type PersonalExpense = {
    id: string;
    name: string;
    amounts: Record<string, number>;
};

export type Household = {
    members: Member[];
    common_expenses: CommonExpense[];
    personal_expenses: PersonalExpense[];
};

/** Coerce an input value ('' while typing, NaN, negative) to a safe amount. */
export const toAmount = (value: unknown): number => {
    const n = Number(value);

    return Number.isFinite(n) && n > 0 ? n : 0;
};

export const totalIncome = (members: Member[]): number =>
    members.reduce((sum, m) => sum + toAmount(m.income), 0);

/** Each member's share of the household income, 0..1. */
export const shares = (members: Member[]): Record<string, number> => {
    const total = totalIncome(members);

    return Object.fromEntries(
        members.map((m) => [
            m.id,
            total > 0 ? toAmount(m.income) / total : 1 / members.length,
        ]),
    );
};

export const commonTotal = (expenses: CommonExpense[]): number =>
    expenses.reduce((sum, e) => sum + toAmount(e.amount), 0);

/** A member's part of one shared expense. */
export const commonPart = (
    expense: CommonExpense,
    member: Member,
    members: Member[],
): number => toAmount(expense.amount) * shares(members)[member.id];

export const commonSubtotal = (
    expenses: CommonExpense[],
    member: Member,
    members: Member[],
): number => commonTotal(expenses) * shares(members)[member.id];

export const personalSubtotal = (
    expenses: PersonalExpense[],
    member: Member,
): number =>
    expenses.reduce((sum, e) => sum + toAmount(e.amounts[member.id]), 0);

export const personalTotal = (expenses: PersonalExpense[]): number =>
    expenses.reduce(
        (sum, e) =>
            sum + Object.values(e.amounts).reduce((s, a) => s + toAmount(a), 0),
        0,
    );

/** What a member keeps before housing: income - shared part - personal. */
export const remaining = (member: Member, h: Household): number =>
    toAmount(member.income) -
    commonSubtotal(h.common_expenses, member, h.members) -
    personalSubtotal(h.personal_expenses, member);

export const formatMoney = (value: number, locale: string): string =>
    new Intl.NumberFormat(locale === 'fr' ? 'fr-CA' : 'en-CA', {
        style: 'currency',
        currency: 'CAD',
        maximumFractionDigits: 0,
    }).format(value);

export const formatPercent = (value: number, locale: string): string =>
    new Intl.NumberFormat(locale === 'fr' ? 'fr-CA' : 'en-CA', {
        style: 'percent',
        minimumFractionDigits: 1,
        maximumFractionDigits: 1,
    }).format(value);

export type Housing = {
    id: string;
    name: string;
    rent: number;
    parking: number;
    heating: 'included' | 'paid';
    heating_amount: number;
    one_time: number;
};

/** Share of household income housing may take; above it, it's too much. */
export const HOUSING_LIMIT = 0.3;

/** Real monthly cost: rent + parking + heating when the household pays it. */
export const housingMonthly = (h: Housing): number =>
    toAmount(h.rent) +
    toAmount(h.parking) +
    (h.heating === 'paid' ? toAmount(h.heating_amount) : 0);

export type VerdictTier =
    | 'wellUnder'
    | 'roomy'
    | 'ok'
    | 'thin'
    | 'justOver'
    | 'over';

export type Verdict = {
    housing: Housing;
    monthly: number;
    /** Housing cost over household income, 0..1+ */
    ratio: number;
    pass: boolean;
    tier: VerdictTier;
    /** Positive: room under the limit. Negative: how much over. */
    margin: number;
    parts: {
        member: Member;
        share: number;
        part: number;
        ownRatio: number;
        left: number;
    }[];
};

const tierFor = (ratio: number): VerdictTier => {
    if (ratio < 0.15) return 'wellUnder';
    if (ratio < 0.2) return 'roomy';
    if (ratio < 0.25) return 'ok';
    if (ratio <= HOUSING_LIMIT) return 'thin';
    if (ratio <= HOUSING_LIMIT + 0.05) return 'justOver';

    return 'over';
};

export const verdictFor = (housing: Housing, h: Household): Verdict => {
    const monthly = housingMonthly(housing);
    const income = totalIncome(h.members);
    const ratio = income > 0 ? monthly / income : monthly > 0 ? Infinity : 0;
    const memberShares = shares(h.members);

    return {
        housing,
        monthly,
        ratio,
        pass: ratio <= HOUSING_LIMIT,
        tier: tierFor(ratio),
        margin: income * HOUSING_LIMIT - monthly,
        parts: h.members.map((member) => {
            const part = monthly * memberShares[member.id];

            return {
                member,
                share: memberShares[member.id],
                part,
                ownRatio:
                    toAmount(member.income) > 0
                        ? part / toAmount(member.income)
                        : 0,
                left: remaining(member, h) - part,
            };
        }),
    };
};

/** Cheapest relative to income first. */
export const rankedVerdicts = (housings: Housing[], h: Household): Verdict[] =>
    housings
        .map((housing) => verdictFor(housing, h))
        .sort((a, b) => a.ratio - b.ratio);
