<?php

namespace Database\Seeders;

use App\Models\TransactionCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Define the categories and subcategories
        $categories = [
            [
                'name' => 'Income',
                'description' => 'Category grouping together all cash receipts related to rental.',
                'subcategories' => [
                    [
                        'name' => 'Rents',
                        'description' => 'Collection of basic rents, regardless of the type of lease.',
                    ],
                    [
                        'name' => 'Regularization of charges',
                        'description' => 'Annual adjustment of rental charges.',
                    ],
                    [
                        'name' => 'Other income (to be specified)',
                        'description' => 'To record other types of rental income that are not fixed rents (e.g., additional services billed to the tenant).',
                    ],
                ],
            ],
            [
                'name' => 'Expenses',
                'description' => 'Category grouping together all cash outflows linked to rental management.',
                'subcategories' => [
                    [
                        'name' => 'Property Management',
                        'description' => 'Subcategory of expenses related to building management and tenant relations.',
                        'subcategories' => [
                            [
                                'name' => 'Property management fees',
                                'description' => 'Remuneration of the rental manager for services whose charges are tax deductible.',
                            ],
                            [
                                'name' => 'Provisions for charges',
                                'description' => 'Amounts set aside to cover current management expenses (trustee, etc.).',
                            ],
                            [
                                'name' => 'Provisions for exceptional charges',
                                'description' => 'Amounts set aside for non-current management expenses (exceptional work voted on at the AGM, etc.).',
                            ],
                            [
                                'name' => 'Reserve fund for building works',
                                'description' => 'Contributions paid to feed a fund intended to finance future major works.',
                            ],
                            [
                                'name' => 'Building staff costs',
                                'description' => 'Salaries and expenses of staff assigned to the building (caretakers, concierges, maintenance staff, etc.).',
                            ],
                            [
                                'name' => 'Security and guarding costs',
                                'description' => 'Expenses related to the security of the premises (security guards, alarms, surveillance systems, etc.).',
                            ],
                            [
                                'name' => 'Cleaning fees',
                                'description' => 'Maintenance of common areas and premises.',
                            ],
                            [
                                'name' => 'Maintenance of the building and equipment',
                                'description' => 'Expenses for routine maintenance and upkeep of building equipment which may be re-invoiced to tenants depending on the type of lease.',
                            ],
                            [
                                'name' => 'Maintenance of parking areas',
                                'description' => 'Specific expenses for the maintenance of parking lots (cleaning, marking, repairs, etc.).',
                            ],
                            [
                                'name' => 'Other property management expenses',
                                'description' => 'Other types of Property Management expenses not classified in the previous categories.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Rental charges (Utilities)',
                        'description' => 'Subcategory of expenses related to shared or individual consumption and services billed to tenants.',
                        'subcategories' => [
                            [
                                'name' => 'Heating',
                                'description' => 'Central or collective heating expenses.',
                            ],
                            [
                                'name' => 'Water',
                                'description' => 'Consumption of cold and hot water.',
                            ],
                            [
                                'name' => 'Electricity',
                                'description' => 'Electricity consumption of common areas or collective services.',
                            ],
                            [
                                'name' => 'Gas',
                                'description' => 'Gas consumption for public services.',
                            ],
                            [
                                'name' => 'Internet',
                                'description' => 'Internet subscription for collective services or included in charges.',
                            ],
                            [
                                'name' => 'Phone',
                                'description' => 'Telephone subscription for collective services or included in charges.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Property Taxes & Insurance',
                        'description' => 'Subcategory of expenses related to property taxes and property insurance.',
                        'subcategories' => [
                            [
                                'name' => 'Owner\'s Insurance',
                                'description' => 'Insurance premiums to guarantee the property (building damage, multi-risk building, etc.).',
                            ],
                            [
                                'name' => 'Property taxes',
                                'description' => 'All property taxes related to the property (property tax, CFE, CVAE, etc.).',
                            ],
                            [
                                'name' => 'Household waste collection taxes',
                                'description' => 'Tax related to waste collection.',
                            ],
                            [
                                'name' => 'Parking taxes and fees',
                                'description' => 'Parking-specific taxes (if applicable).',
                            ],
                            [
                                'name' => 'Other taxes',
                                'description' => 'Other types of taxes and duties not classified in the previous categories.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Financial Expenses',
                        'description' => 'Subcategory of expenses related to the financial costs of rental management.',
                        'subcategories' => [
                            [
                                'name' => 'Loan repayments: Real estate',
                                'description' => 'Monthly mortgage payments.',
                            ],
                            [
                                'name' => 'Loan repayments: Works',
                                'description' => 'Monthly payments for loans taken out for work.',
                            ],
                            [
                                'name' => 'Loan repayments: Others',
                                'description' => 'Monthly payments for other types of loans related to rental management.',
                            ],
                            [
                                'name' => 'Loan interest',
                                'description' => 'Portion of monthly payments corresponding to interest.',
                            ],
                            [
                                'name' => 'Bank charges',
                                'description' => 'Account management fees, commissions, etc.',
                            ],
                            [
                                'name' => 'Penalties and fines',
                                'description' => 'Late payment penalties, tax or administrative fines related to the management of the property.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Legal & Administrative Fees',
                        'description' => 'Subcategory of expenses related to legal and administrative aspects.',
                        'subcategories' => [
                            [
                                'name' => 'Fees for acts',
                                'description' => 'Notary fees, registration fees for deeds (leases, etc.).',
                            ],
                            [
                                'name' => 'Legal fees',
                                'description' => 'Lawyer\'s fees, bailiff\'s fees, court costs (tenant disputes, etc.).',
                            ],
                            [
                                'name' => 'Postage costs',
                                'description' => 'Postage, registered mail, etc.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Marketing & Tenant Relations',
                        'description' => 'Subcategory of expenses related to the promotion of the property and management of the relationship with tenants.',
                        'subcategories' => [
                            [
                                'name' => 'Advertising and marketing costs',
                                'description' => 'Rental advertisements, advertising materials, etc.',
                            ],
                            [
                                'name' => 'Tenant incentive',
                                'description' => 'Financial or material benefits offered to encourage the signing of a lease (e.g.: free month\'s rent).',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Operating Expenses',
                        'description' => 'Subcategory of expenses related to the day-to-day operation and maintenance of the property.',
                        'subcategories' => [
                            [
                                'name' => 'Professional fees',
                                'description' => 'Remuneration of external service providers (accountants, consultants, etc.).',
                            ],
                            [
                                'name' => 'Travel expenses',
                                'description' => 'Travel related to rental management (visits, meetings, on-site interventions, etc.).',
                            ],
                            [
                                'name' => 'Equipment and supplies',
                                'description' => 'Small tools, office supplies, cleaning products, etc.',
                            ],
                            [
                                'name' => 'Furniture and equipment',
                                'description' => 'Acquisition of furniture or equipment for common areas or rented premises.',
                            ],
                            [
                                'name' => 'Salaries and social security contributions',
                                'description' => 'Remuneration of administrative staff dedicated to rental management (if applicable).',
                            ],
                            [
                                'name' => 'Regularization of charges',
                                'description' => 'Annual adjustment of rental charges.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Works',
                        'description' => 'Expenses related to work on the property.',
                        'subcategories' => [
                            [
                                'name' => 'Repairs and maintenance of the property',
                                'description' => 'Routine repair and maintenance work on the building and equipment.',
                            ],
                            [
                                'name' => 'Improvements to the property',
                                'description' => 'Work aimed at improving the comfort, performance or value of the property (renovation, modernization, etc.).',
                            ],
                            [
                                'name' => 'Construction/Extension of the property',
                                'description' => 'New construction, reconstruction or expansion work.',
                            ],
                            [
                                'name' => 'Other types of work',
                                'description' => 'To record other types of work not classified in the previous categories.',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Refunds',
                        'description' => 'Category grouping together cash outflows corresponding to refunds of sums to tenants.',
                        'subcategories' => [
                            [
                                'name' => 'Security Deposit Refunds',
                                'description' => 'Return of security deposits to outgoing tenants.',
                            ],
                            [
                                'name' => 'Tenant balance refunds',
                                'description' => 'Reimbursement of amounts owed to tenants (overpayments, etc.).',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Account Operations',
                        'description' => 'Category grouping together internal fund movements in the rental management bank account.',
                        'subcategories' => [
                            [
                                'name' => 'Current account withdrawals',
                                'description' => 'Cash withdrawals or outgoing transfers for rental management purposes (without a specific destination in the expense categories).',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Insert categories and subcategories
        foreach ($categories as $category) {
            $parentCategory = TransactionCategory::create([
                'name' => $category['name'],
                'description' => $category['description'],
                'parent_id' => null,
            ]);

            if (isset($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    if (isset($subcategory['subcategories'])) {
                        $subParentCategory = TransactionCategory::create([
                            'name' => $subcategory['name'],
                            'description' => $subcategory['description'],
                            'parent_id' => $parentCategory->id,
                        ]);

                        foreach ($subcategory['subcategories'] as $subSubcategory) {
                            TransactionCategory::create([
                                'name' => $subSubcategory['name'],
                                'description' => $subSubcategory['description'],
                                'parent_id' => $subParentCategory->id,
                            ]);
                        }
                    } else {
                        TransactionCategory::create([
                            'name' => $subcategory['name'],
                            'description' => $subcategory['description'],
                            'parent_id' => $parentCategory->id,
                        ]);
                    }
                }
            }
        }
    }
}
