import { startStimulusApp } from "@symfony/stimulus-bundle";
import { registerControllers } from "vite-plugin-symfony/stimulus/helpers";
import "./styles/app.css";

const app = startStimulusApp();

registerControllers(
  app,
  import.meta.glob<StimulusControllerInfosImport>(
    "./controllers/*_controller.ts",
    {
      query: "?stimulus",
      /**
       * always true, the `lazy` behavior is managed internally with
       * import.meta.stimulusFetch (see reference)
       */
      eager: true,
    },
  ),
);

console.log(app);
