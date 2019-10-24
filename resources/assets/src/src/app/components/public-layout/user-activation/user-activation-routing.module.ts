import { AuthActivationGuard } from './../../../http/guards/auth-activation.guard';
import { ActivatedComponent } from './activated/activated.component';
import { NonauthGuard } from './../../../http/guards/nonauth.guard';
import { ActivationComponent } from './activation/activation.component';
import { Routes, RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { MigrateUserComponent } from './migrate-user/migrate-user.component';

const routes: Routes = [
  { path: '', redirectTo: 'activation', pathMatch: 'full' },
  {
    path: 'redirect/activation',
    component: ActivatedComponent,
  },
  {
    path: 'activation',
    component: ActivationComponent,
    canActivate: [AuthActivationGuard],
  },
  {
    path: 'migrate',
    component: MigrateUserComponent,
    canActivate: [NonauthGuard],
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UserActivationRoutingModule { }
