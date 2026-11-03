import type { Locale } from './index';

export type LegalSlug = 'terms' | 'privacy' | 'legal-notice';

export type LegalDoc = {
    title: string;
    intro?: string;
    sections: { title: string; paragraphs: string[] }[];
};

export const legalSlugs: LegalSlug[] = ['terms', 'privacy', 'legal-notice'];

/*
 * Placeholders ({operator}, {email}, {address}, {host}) are filled from
 * config/legal.php by the Legal page.
 */
const fr: Record<LegalSlug, LegalDoc> = {
    terms: {
        title: "Conditions d'utilisation et de vente",
        intro: "En créant un compte ou en utilisant Abrio, tu acceptes les conditions ci-dessous. Elles tiennent lieu de conditions générales d'utilisation (CGU) et de vente (CGV).",
        sections: [
            {
                title: '1. Le service',
                paragraphs: [
                    "Abrio est un outil en ligne qui compare des logements selon le budget d'un ménage : part de chacun, coût réel du logement et reste à vivre. Il est exploité par {operator} (« nous »).",
                    "Abrio est un outil d'estimation. Les résultats reposent sur les montants que tu entres et sur la règle des 30 % du revenu net. Ils ne constituent ni un conseil financier, ni un conseil juridique, ni une garantie d'obtenir un logement ou un prêt.",
                ],
            },
            {
                title: '2. Ton compte',
                paragraphs: [
                    "Tu dois avoir 18 ans ou plus pour créer un compte. Les renseignements fournis doivent être exacts et tu es responsable de la confidentialité de ton mot de passe.",
                    "Un compte est personnel. Tu peux inviter jusqu'à 4 personnes à modifier ton budget si ton offre le permet : tu restes responsable de ces invitations.",
                ],
            },
            {
                title: '3. Offre gratuite et version illimitée',
                paragraphs: [
                    "L'offre gratuite permet de comparer jusqu'à 2 logements. La version illimitée est un achat unique (prix affiché au moment de l'achat, taxes applicables en sus au besoin) : elle retire la limite de logements et ouvre le partage du budget et le lien public. Il n'y a aucun abonnement ni renouvellement automatique.",
                    "Le paiement est traité par Stripe. Nous ne voyons ni ne conservons ton numéro de carte. Une facture est disponible dans tes paramètres.",
                ],
            },
            {
                title: '4. Remboursement',
                paragraphs: [
                    "Si la version illimitée ne te convient pas, écris-nous à {email} dans les 14 jours suivant l'achat : nous te remboursons, sans justification. Passé ce délai, l'achat n'est plus remboursable, sauf si la loi applicable l'exige.",
                    "Ce délai s'ajoute aux droits que la loi te reconnaît déjà comme consommateur.",
                ],
            },
            {
                title: '5. Utilisation acceptable',
                paragraphs: [
                    "Tu t'engages à ne pas : perturber le service ou tenter d'y accéder sans autorisation; utiliser des moyens automatisés pour en extraire les données; créer de faux comptes; ou t'en servir à des fins illégales.",
                    "Nous pouvons suspendre ou bloquer l'accès d'un compte, d'une adresse courriel ou d'une adresse IP en cas d'abus, de fraude ou de violation de ces conditions. Si tu penses que c'est une erreur, écris-nous.",
                ],
            },
            {
                title: '6. Propriété intellectuelle',
                paragraphs: [
                    "Abrio, son nom, son logo, son code et ses textes nous appartiennent. Nous te donnons un droit d'usage personnel, non exclusif et non transférable. Les données que tu saisis restent les tiennes.",
                ],
            },
            {
                title: '7. Disponibilité et responsabilité',
                paragraphs: [
                    "Nous faisons de notre mieux pour que le service soit disponible, sans garantie d'accès continu ni d'absence d'erreur. Dans les limites permises par la loi, nous ne sommes pas responsables des décisions prises à partir des résultats d'Abrio, ni des dommages indirects. Notre responsabilité totale est limitée au montant que tu nous as payé.",
                    "Rien dans ces conditions ne limite les droits que la loi t'accorde comme consommateur, ni une responsabilité qui ne peut être exclue.",
                ],
            },
            {
                title: '8. Fermeture du compte',
                paragraphs: [
                    "Tu peux supprimer ton compte à tout moment dans tes paramètres. Tes données sont alors supprimées, sous réserve de ce que nous devons conserver (par exemple les factures).",
                ],
            },
            {
                title: '9. Modifications',
                paragraphs: [
                    "Nous pouvons modifier ces conditions. En cas de changement important, nous t'en informerons raisonnablement à l'avance. Continuer à utiliser Abrio après l'entrée en vigueur du changement vaut acceptation.",
                ],
            },
            {
                title: '10. Droit applicable',
                paragraphs: [
                    "Ces conditions sont régies par les lois du Québec et les lois fédérales du Canada qui s'y appliquent. Les tribunaux du district judiciaire de Québec sont compétents, sans préjudice de ton droit de saisir le tribunal de ton domicile lorsque la loi le prévoit.",
                    "Une question? Écris-nous à {email}.",
                ],
            },
        ],
    },
    privacy: {
        title: 'Politique de confidentialité et témoins (cookies)',
        intro: "Nous collectons le strict nécessaire pour faire fonctionner Abrio. Voici quoi, pourquoi et comment exercer tes droits (notamment en vertu de la Loi 25 du Québec et de la LPRPDE).",
        sections: [
            {
                title: '1. Responsable',
                paragraphs: [
                    "{operator} est responsable des renseignements personnels collectés par Abrio. Pour toute question ou demande : {email}.",
                ],
            },
            {
                title: '2. Ce que nous collectons',
                paragraphs: [
                    "Compte : nom d'affichage, courriel, mot de passe (chiffré), pays et province de résidence, langue.",
                    "Ton budget : les revenus, charges et logements que tu entres, ainsi que les personnes de ton ménage.",
                    "Sécurité : l'adresse IP utilisée à l'inscription et à la dernière connexion, pour prévenir les abus et la fraude et pour appliquer les blocages au besoin.",
                    "Consentements : la date à laquelle tu as accepté les conditions et, si tu l'as choisi, la date de ton inscription aux courriels promotionnels.",
                    "Paiement : géré par Stripe. Nous recevons seulement la confirmation du paiement, pas ton numéro de carte.",
                    "Contact : le nom, le courriel et le message que tu nous envoies par le formulaire.",
                ],
            },
            {
                title: '3. Pourquoi',
                paragraphs: [
                    "Pour fournir le service (calculs, partage du budget, compte), traiter ton achat, sécuriser les accès, te répondre et envoyer les courriels liés à ton compte (code de vérification, invitations, alertes de sécurité).",
                    "Le pays et la province servent uniquement à des statistiques anonymes. Nous ne vendons pas tes renseignements et nous ne faisons pas de publicité ciblée.",
                ],
            },
            {
                title: '4. Courriels promotionnels',
                paragraphs: [
                    "Nous t'envoyons des nouvelles ou des offres seulement si tu as coché la case prévue à cet effet. Tu peux changer d'avis à tout moment dans Paramètres > Profil, ou en écrivant à {email}. Les courriels liés au fonctionnement de ton compte ne sont pas des courriels promotionnels.",
                ],
            },
            {
                title: '5. Témoins (cookies)',
                paragraphs: [
                    "Abrio utilise uniquement des témoins essentiels, qui ne demandent pas de consentement : session et jeton de sécurité (connexion et protection contre les attaques CSRF), préférences d'affichage (thème, barre latérale) et mémorisation de ton choix concernant le bandeau d'information.",
                    "Nous n'utilisons aucun témoin publicitaire ni de suivi. Si cela change, nous te demanderons ton accord avant de les activer.",
                    "Les polices de caractères sont chargées depuis Google Fonts : ton navigateur contacte alors les serveurs de Google, qui reçoivent ton adresse IP.",
                ],
            },
            {
                title: '6. Avec qui nous partageons',
                paragraphs: [
                    "Seulement avec les fournisseurs nécessaires au service : Stripe (paiement), notre hébergeur ({host}), notre service d'envoi de courriels et Google Fonts. Ces fournisseurs peuvent se trouver hors du Québec, notamment aux États-Unis, et sont tenus de protéger tes renseignements.",
                    "Les personnes que tu invites voient le budget du ménage que tu partages avec elles. Nous pouvons aussi divulguer des renseignements si la loi l'exige.",
                ],
            },
            {
                title: '7. Durée de conservation',
                paragraphs: [
                    "Nous conservons tes renseignements tant que ton compte existe. Si tu le supprimes, ils sont effacés, sauf ceux que nous devons garder (factures et registres d'achat) et les adresses bloquées pour cause d'abus.",
                ],
            },
            {
                title: '8. Tes droits',
                paragraphs: [
                    "Tu peux demander l'accès à tes renseignements, leur correction, leur suppression, retirer ton consentement ou demander une copie portable de tes données. Écris à {email} : nous répondons dans un délai de 30 jours.",
                    "Tu peux aussi porter plainte à la Commission d'accès à l'information du Québec ou au Commissariat à la protection de la vie privée du Canada.",
                ],
            },
            {
                title: '9. Sécurité',
                paragraphs: [
                    "Mots de passe chiffrés, connexion sécurisée (HTTPS), authentification à deux facteurs et clés d'accès disponibles. Aucun système n'est infaillible : en cas d'incident présentant un risque sérieux, nous t'en informerons.",
                ],
            },
        ],
    },
    'legal-notice': {
        title: 'Mentions légales',
        sections: [
            {
                title: 'Éditeur du site',
                paragraphs: [
                    "Abrio est édité par {operator}, entrepreneur individuel.",
                    "Adresse : {address}",
                    "Courriel : {email}",
                ],
            },
            {
                title: 'Directeur de la publication',
                paragraphs: ['{operator}.'],
            },
            {
                title: 'Hébergement',
                paragraphs: ['{host}.'],
            },
            {
                title: 'Paiement',
                paragraphs: [
                    'Les paiements sont traités par Stripe, un prestataire de services de paiement externe.',
                ],
            },
            {
                title: 'Propriété intellectuelle',
                paragraphs: [
                    "Le nom Abrio, le logo, le code et les textes du site sont protégés. Toute reproduction sans autorisation écrite est interdite.",
                ],
            },
            {
                title: 'Contact',
                paragraphs: [
                    "Pour toute question, demande liée à tes renseignements personnels ou signalement : {email}.",
                ],
            },
        ],
    },
};

const en: Record<LegalSlug, LegalDoc> = {
    terms: {
        title: 'Terms of use and sale',
        intro: 'By creating an account or using Abrio, you accept the terms below. They serve as both our terms of use and our terms of sale.',
        sections: [
            {
                title: '1. The service',
                paragraphs: [
                    'Abrio is an online tool that compares apartments against a household budget: each person’s share, the real cost of housing and what is left each month. It is operated by {operator} (“we”).',
                    'Abrio is an estimation tool. Results rely on the amounts you enter and on the 30% of net income rule. They are not financial or legal advice, nor a guarantee of getting an apartment or a loan.',
                ],
            },
            {
                title: '2. Your account',
                paragraphs: [
                    'You must be 18 or older to create an account. The information you provide must be accurate, and you are responsible for keeping your password confidential.',
                    'An account is personal. You may invite up to 4 people to edit your budget if your plan allows it: you remain responsible for those invitations.',
                ],
            },
            {
                title: '3. Free plan and unlimited version',
                paragraphs: [
                    'The free plan lets you compare up to 2 apartments. The unlimited version is a one-time purchase (price shown at checkout, applicable taxes extra where required): it removes the apartment limit and enables budget sharing and the public link. There is no subscription and no automatic renewal.',
                    'Payment is processed by Stripe. We never see or store your card number. An invoice is available in your settings.',
                ],
            },
            {
                title: '4. Refunds',
                paragraphs: [
                    'If the unlimited version is not right for you, write to {email} within 14 days of purchase and we will refund you, no questions asked. After that, the purchase is no longer refundable unless applicable law requires it.',
                    'This period is in addition to the rights the law already gives you as a consumer.',
                ],
            },
            {
                title: '5. Acceptable use',
                paragraphs: [
                    'You agree not to: disrupt the service or try to access it without authorization; use automated means to extract its data; create fake accounts; or use it for unlawful purposes.',
                    'We may suspend or block access for an account, an email address or an IP address in case of abuse, fraud or breach of these terms. If you think this is a mistake, write to us.',
                ],
            },
            {
                title: '6. Intellectual property',
                paragraphs: [
                    'Abrio, its name, logo, code and texts belong to us. We grant you a personal, non-exclusive, non-transferable right of use. The data you enter remains yours.',
                ],
            },
            {
                title: '7. Availability and liability',
                paragraphs: [
                    'We do our best to keep the service available, without guaranteeing uninterrupted access or an error-free service. To the extent permitted by law, we are not liable for decisions made based on Abrio’s results, nor for indirect damages. Our total liability is limited to the amount you paid us.',
                    'Nothing in these terms limits the rights the law gives you as a consumer, or any liability that cannot be excluded.',
                ],
            },
            {
                title: '8. Closing your account',
                paragraphs: [
                    'You can delete your account at any time in your settings. Your data is then deleted, except what we must keep (for example invoices).',
                ],
            },
            {
                title: '9. Changes',
                paragraphs: [
                    'We may change these terms. For significant changes, we will give you reasonable notice. Continuing to use Abrio after the change takes effect means you accept it.',
                ],
            },
            {
                title: '10. Governing law',
                paragraphs: [
                    'These terms are governed by the laws of Quebec and the federal laws of Canada applicable there. The courts of the judicial district of Quebec have jurisdiction, without prejudice to your right to bring proceedings where you live when the law allows it.',
                    'Questions? Write to {email}.',
                ],
            },
        ],
    },
    privacy: {
        title: 'Privacy and cookie policy',
        intro: 'We collect only what is needed to run Abrio. Here is what, why, and how to exercise your rights (notably under Quebec’s Law 25 and PIPEDA).',
        sections: [
            {
                title: '1. Who is responsible',
                paragraphs: [
                    '{operator} is responsible for the personal information collected by Abrio. For any question or request: {email}.',
                ],
            },
            {
                title: '2. What we collect',
                paragraphs: [
                    'Account: display name, email, password (hashed), country and province of residence, language.',
                    'Your budget: the incomes, expenses and apartments you enter, and the people in your household.',
                    'Security: the IP address used when you sign up and at your last login, to prevent abuse and fraud and to enforce blocks when needed.',
                    'Consents: the date you accepted the terms and, if you chose to, the date you subscribed to promotional emails.',
                    'Payment: handled by Stripe. We only receive confirmation of payment, not your card number.',
                    'Contact: the name, email and message you send through the form.',
                ],
            },
            {
                title: '3. Why',
                paragraphs: [
                    'To provide the service (calculations, budget sharing, account), process your purchase, secure access, answer you and send emails related to your account (verification code, invitations, security alerts).',
                    'Country and province are used only for anonymous statistics. We do not sell your information and we do not run targeted advertising.',
                ],
            },
            {
                title: '4. Promotional emails',
                paragraphs: [
                    'We send news or offers only if you ticked the box for it. You can change your mind at any time in Settings > Profile, or by writing to {email}. Emails needed to run your account are not promotional emails.',
                ],
            },
            {
                title: '5. Cookies',
                paragraphs: [
                    'Abrio only uses essential cookies, which do not require consent: session and security token (login and CSRF protection), display preferences (theme, sidebar) and remembering your choice about the information banner.',
                    'We use no advertising or tracking cookies. If that changes, we will ask for your consent before turning them on.',
                    'Fonts are loaded from Google Fonts: your browser then contacts Google’s servers, which receive your IP address.',
                ],
            },
            {
                title: '6. Who we share with',
                paragraphs: [
                    'Only the providers needed for the service: Stripe (payment), our host ({host}), our email delivery service and Google Fonts. These providers may be located outside Quebec, including in the United States, and are required to protect your information.',
                    'People you invite can see the household budget you share with them. We may also disclose information if the law requires it.',
                ],
            },
            {
                title: '7. Retention',
                paragraphs: [
                    'We keep your information as long as your account exists. If you delete it, it is erased, except what we must keep (invoices and purchase records) and addresses blocked for abuse.',
                ],
            },
            {
                title: '8. Your rights',
                paragraphs: [
                    'You may request access to your information, correction, deletion, withdraw your consent, or ask for a portable copy of your data. Write to {email}: we reply within 30 days.',
                    'You may also file a complaint with Quebec’s Commission d’accès à l’information or the Office of the Privacy Commissioner of Canada.',
                ],
            },
            {
                title: '9. Security',
                paragraphs: [
                    'Hashed passwords, secure connection (HTTPS), two-factor authentication and passkeys available. No system is infallible: in case of an incident presenting a serious risk, we will let you know.',
                ],
            },
        ],
    },
    'legal-notice': {
        title: 'Legal notice',
        sections: [
            {
                title: 'Site publisher',
                paragraphs: [
                    'Abrio is published by {operator}, sole proprietor.',
                    'Address: {address}',
                    'Email: {email}',
                ],
            },
            {
                title: 'Publication director',
                paragraphs: ['{operator}.'],
            },
            {
                title: 'Hosting',
                paragraphs: ['{host}.'],
            },
            {
                title: 'Payment',
                paragraphs: [
                    'Payments are processed by Stripe, an external payment service provider.',
                ],
            },
            {
                title: 'Intellectual property',
                paragraphs: [
                    'The Abrio name, logo, code and texts on the site are protected. Any reproduction without written permission is prohibited.',
                ],
            },
            {
                title: 'Contact',
                paragraphs: [
                    'For any question, request about your personal information or report: {email}.',
                ],
            },
        ],
    },
};

export const legalDocs: Record<Locale, Record<LegalSlug, LegalDoc>> = {
    fr,
    en,
};

export const legalUpdatedLabel: Record<Locale, string> = {
    fr: 'Dernière mise à jour : 2 novembre 2026',
    en: 'Last updated: November 2, 2026',
};
