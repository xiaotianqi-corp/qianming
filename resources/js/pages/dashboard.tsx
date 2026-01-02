import { CardOverview } from '@/components/app/card-overview';
import { CashFlowOverview } from '@/components/app/cash-flow-overview';
import { IncomeReliability } from '@/components/app/income-reliability';
import { MonthlyCashFlow } from '@/components/app/kpis/monthly-cash-flow';
import { NetWorth } from '@/components/app/kpis/net-worth';
import { PrimaryAccount } from '@/components/app/kpis/primary-account';
import { SavingsRate } from '@/components/app/kpis/savings-rate';
import { SpendingBreakdown } from '@/components/app/spending-breakdown';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
          <Head title="Dashboard" />
          <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Tabs className="gap-4" defaultValue="overview">
                <TabsList>
                  <TabsTrigger value="overview">Overview</TabsTrigger>
                  <TabsTrigger disabled value="activity">
                    Activity
                  </TabsTrigger>
                  <TabsTrigger disabled value="insights">
                    Insights
                  </TabsTrigger>
                  <TabsTrigger disabled value="utilities">
                    Utilities
                  </TabsTrigger>
                </TabsList>

                <TabsContent value="overview">
                  <div className="flex flex-col gap-4 **:data-[slot=card]:shadow-xs">
                    <div className="grid grid-cols-1 gap-4 *:data-[slot=card]:gap-2 *:data-[slot=card]:bg-linear-to-t *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                      <PrimaryAccount />
                      <NetWorth />
                      <MonthlyCashFlow />
                      <SavingsRate />
                    </div>

                    <div className="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
                      <div className="flex flex-col gap-4">
                        <CashFlowOverview />

                        <div className="grid h-full grid-cols-1 gap-4 lg:grid-cols-2">
                          <SpendingBreakdown />
                          <IncomeReliability />
                        </div>
                      </div>
                      <CardOverview/>
                    </div>
                  </div>
                </TabsContent>
              </Tabs>
            </div>
        </AppLayout>
    );
}
