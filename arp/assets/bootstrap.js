import { startStimulusApp } from "@symfony/stimulus-bridge";

// Registre les contrôleurs de ton application
export const app = startStimulusApp(
  require.context(
    "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
    true,
    /\.[jt]sx?$/
  )
);
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
