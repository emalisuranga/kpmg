import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { NameChangeComponent } from './name-change.component';
import { WorkflowGuard } from './workflow/workflow-guard.service';
import { SelectNameChangeComponent } from './select-name-change/select-name-change.component';


const routes: Routes = [
  { path: '', redirectTo: 'change', pathMatch: 'full' },
  {
    path: 'change',
    component: NameChangeComponent,
    children: [
      { path: 'select', component: SelectNameChangeComponent },
      { path: '', redirectTo: 'select', pathMatch: 'full' },
      { path: '**', component: NameChangeComponent }
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class NameChangeRoutingModule { }
